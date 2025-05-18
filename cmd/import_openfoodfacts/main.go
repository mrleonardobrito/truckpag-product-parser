package main

import (
	"bufio"
	"compress/gzip"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"sync"
	"time"

	"github.com/joho/godotenv"
	"go.mongodb.org/mongo-driver/bson"
	"go.mongodb.org/mongo-driver/bson/primitive"
	"go.mongodb.org/mongo-driver/mongo"
	"go.mongodb.org/mongo-driver/mongo/options"
)

var (
	baseURL         string
	indexURL        string
	storagePath     string
	productsPerFile int
	mongoURI        string
	mongoDatabase   string
	mongoCollection string
	downloadWorkers int
	processWorkers  int
	importTimeLimit int
)

func init() {
	if err := godotenv.Load("../.env"); err != nil {
		log.Printf("Erro ao carregar .env: %v", err)
	}

	baseURL = getEnv("OPENFOODFACTS_BASE_URL", "https://challenges.coode.sh/food/data/json/")
	indexURL = getEnv("OPENFOODFACTS_INDEX_URL", "https://challenges.coode.sh/food/data/json/index.txt")
	storagePath = getEnv("STORAGE_PATH", "cmd/storage/files")
	productsPerFile = getEnvAsInt("PRODUCTS_PER_FILE", 100)
	mongoURI = getEnv("MONGODB_URI", "mongodb://localhost:27017")
	mongoDatabase = getEnv("MONGODB_DATABASE", "product_parser")
	mongoCollection = getEnv("MONGODB_COLLECTION", "products")
	downloadWorkers = getEnvAsInt("DOWNLOAD_WORKERS", 5)
	processWorkers = getEnvAsInt("PROCESS_WORKERS", 5)
	importTimeLimit = getEnvAsInt("IMPORT_TIME_LIMIT_MINUTES", 0)
}

func getEnv(key, defaultValue string) string {
	if value, exists := os.LookupEnv(key); exists {
		return value
	}
	return defaultValue
}

func getEnvAsInt(key string, defaultValue int) int {
	if value, exists := os.LookupEnv(key); exists {
		if intValue, err := strconv.Atoi(value); err == nil {
			return intValue
		}
	}
	return defaultValue
}

type ProductStatus string

const (
	ProductStatusPublished ProductStatus = "published"
	ProductStatusDraft     ProductStatus = "draft"
	ProductStatusTrash     ProductStatus = "trash"
)

type Product struct {
	Code            string        `bson:"code"`
	ImportedT       *int64        `bson:"imported_t,omitempty"`
	Status          ProductStatus `bson:"status"`
	Url             *string       `bson:"url,omitempty"`
	Creator         *string       `bson:"creator,omitempty"`
	CreatedT        *int64        `bson:"created_t,omitempty"`
	LastModifiedT   *int64        `bson:"last_modified_t,omitempty"`
	ProductName     string        `bson:"product_name"`
	Quantity        *string       `bson:"quantity,omitempty"`
	Brands          *string       `bson:"brands,omitempty"`
	Categories      *string       `bson:"categories,omitempty"`
	Labels          *string       `bson:"labels,omitempty"`
	Cities          *string       `bson:"cities,omitempty"`
	PurchasePlaces  *string       `bson:"purchase_places,omitempty"`
	Stores          *string       `bson:"stores,omitempty"`
	IngredientsText *string       `bson:"ingredients_text,omitempty"`
	Traces          *string       `bson:"traces,omitempty"`
	ServingSize     *string       `bson:"serving_size,omitempty"`
	ServingQuantity float64       `bson:"serving_quantity,omitempty"`
	NutriscoreScore int64         `bson:"nutriscore_score,omitempty"`
	NutriscoreGrade *string       `bson:"nutriscore_grade,omitempty"`
	MainCategory    *string       `bson:"main_category,omitempty"`
	ImageUrl        *string       `bson:"image_url,omitempty"`
}

type ImportStatus string

const (
	ImportStatusPending   ImportStatus = "pending"
	ImportStatusRunning   ImportStatus = "running"
	ImportStatusCompleted ImportStatus = "completed"
	ImportStatusFailed    ImportStatus = "failed"
)

type ImportHistory struct {
	ID                primitive.ObjectID `bson:"_id,omitempty"`
	InitTime          time.Time          `bson:"init_time"`
	EndTime           *time.Time         `bson:"end_time,omitempty"`
	Status            ImportStatus       `bson:"status"`
	TotalFiles        int                `bson:"total_files"`
	ProcessedFiles    int                `bson:"processed_files"`
	TotalProducts     int                `bson:"total_products"`
	SuccessfulImports int                `bson:"successful_imports"`
	SkippedImports    int                `bson:"skipped_imports"`
	FailedImports     int                `bson:"failed_imports"`
	ErrorMessage      *string            `bson:"error_message,omitempty"`
	mu                sync.Mutex
}

func (s *ImportHistory) IncrementProcessedFiles() {
	s.mu.Lock()
	s.ProcessedFiles++
	s.mu.Unlock()
}

func (s *ImportHistory) IncrementSuccessfulImports() {
	s.mu.Lock()
	s.SuccessfulImports++
	s.TotalProducts++
	s.mu.Unlock()
}

func (s *ImportHistory) IncrementSkippedImports() {
	s.mu.Lock()
	s.SkippedImports++
	s.mu.Unlock()
}

func (s *ImportHistory) IncrementFailedImports() {
	s.mu.Lock()
	s.FailedImports++
	s.TotalProducts++
	s.mu.Unlock()
}

func downloadFile(url, filepath string) error {
	resp, err := http.Get(url)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return fmt.Errorf("bad status: %s", resp.Status)
	}

	out, err := os.Create(filepath)
	if err != nil {
		return err
	}
	defer out.Close()

	_, err = io.Copy(out, resp.Body)
	return err
}

func extractGzip(src, dst string) error {
	reader, err := os.Open(src)
	if err != nil {
		return err
	}
	defer reader.Close()

	archive, err := gzip.NewReader(reader)
	if err != nil {
		return err
	}
	defer archive.Close()

	target, err := os.Create(dst)
	if err != nil {
		return err
	}
	defer target.Close()

	_, err = io.Copy(target, archive)
	return err
}

func normalizeCode(code string) string {
	normalized := strings.TrimSpace(code)
	normalized = strings.Map(func(r rune) rune {
		if r >= '0' && r <= '9' {
			return r
		}
		return -1
	}, normalized)

	if len(normalized) < 13 {
		normalized = strings.Repeat("0", 13-len(normalized)) + normalized
	}

	return normalized
}

func saveProducts(products []Product, collection *mongo.Collection, history *ImportHistory, historyCollection *mongo.Collection) error {
	var operations []mongo.WriteModel
	successfulImports := len(products)
	failedImports := 0
	skippedImports := 0

	for _, product := range products {
		setFields := bson.M{}
		unsetFields := bson.M{}

		setFields["code"] = product.Code
		setFields["status"] = product.Status
		setFields["product_name"] = product.ProductName
		setFields["imported_t"] = product.ImportedT

		optionalFields := map[string]interface{}{
			"url":              product.Url,
			"creator":          product.Creator,
			"created_t":        product.CreatedT,
			"last_modified_t":  product.LastModifiedT,
			"quantity":         product.Quantity,
			"brands":           product.Brands,
			"categories":       product.Categories,
			"labels":           product.Labels,
			"cities":           product.Cities,
			"purchase_places":  product.PurchasePlaces,
			"stores":           product.Stores,
			"ingredients_text": product.IngredientsText,
			"traces":           product.Traces,
			"serving_size":     product.ServingSize,
			"serving_quantity": product.ServingQuantity,
			"nutriscore_score": product.NutriscoreScore,
			"nutriscore_grade": product.NutriscoreGrade,
			"main_category":    product.MainCategory,
			"image_url":        product.ImageUrl,
		}

		for k, v := range optionalFields {
			switch val := v.(type) {
			case *string:
				if val != nil {
					setFields[k] = *val
				} else {
					unsetFields[k] = ""
				}
			case *int64:
				if val != nil {
					setFields[k] = *val
				} else {
					unsetFields[k] = ""
				}
			}
		}

		model := mongo.NewInsertOneModel().
			SetDocument(bson.M{
				"code":             product.Code,
				"status":           product.Status,
				"product_name":     product.ProductName,
				"imported_t":       product.ImportedT,
				"url":              product.Url,
				"creator":          product.Creator,
				"created_t":        product.CreatedT,
				"last_modified_t":  product.LastModifiedT,
				"quantity":         product.Quantity,
				"brands":           product.Brands,
				"categories":       product.Categories,
				"labels":           product.Labels,
				"cities":           product.Cities,
				"purchase_places":  product.PurchasePlaces,
				"stores":           product.Stores,
				"ingredients_text": product.IngredientsText,
				"traces":           product.Traces,
				"serving_size":     product.ServingSize,
				"serving_quantity": product.ServingQuantity,
				"nutriscore_score": product.NutriscoreScore,
				"nutriscore_grade": product.NutriscoreGrade,
				"main_category":    product.MainCategory,
				"image_url":        product.ImageUrl,
			})
		operations = append(operations, model)
	}

	if len(operations) > 0 {
		result, err := collection.BulkWrite(context.TODO(), operations)
		totalSuccessful := int(result.InsertedCount)
		totalSkipped := 0
		totalFailed := 0

		if err != nil {
			if bulkErr, ok := err.(mongo.BulkWriteException); ok {
				for _, writeErr := range bulkErr.WriteErrors {
					idx := writeErr.Index
					op := operations[idx]
					_, singleErr := collection.InsertOne(context.TODO(), op.(*mongo.InsertOneModel).Document)
					if singleErr != nil {
						if mongo.IsDuplicateKeyError(singleErr) {
							totalSkipped++
						} else {
							totalFailed++
							log.Printf("Erro ao inserir produto individualmente: %v", singleErr)
						}
					} else {
						totalSuccessful++
					}
				}
			} else if mongo.IsDuplicateKeyError(err) {
				log.Printf("Erro de duplicidade de chave ao importar produtos: %v", err)
				totalSkipped = len(operations)
				totalSuccessful = 0
			} else {
				log.Printf("Erro ao importar produtos: %v", err)
				totalFailed = len(operations)
				totalSuccessful = 0
			}
		}

		successfulImports = totalSuccessful
		skippedImports = totalSkipped
		failedImports = totalFailed
	}

	history.mu.Lock()
	history.SuccessfulImports += successfulImports
	history.FailedImports += failedImports
	history.SkippedImports += skippedImports
	history.TotalProducts += successfulImports + failedImports + skippedImports
	history.mu.Unlock()

	if err := updateImportHistory(historyCollection, history); err != nil {
		log.Printf("Erro ao atualizar histórico: %v", err)
	}

	return nil
}

func processFile(filename string, collection *mongo.Collection, history *ImportHistory, historyCollection *mongo.Collection) error {
	extractedPath := filepath.Join(storagePath, strings.TrimSuffix(filename, ".gz"))

	file, err := os.Open(extractedPath)
	if err != nil {
		return err
	}
	defer file.Close()

	scanner := bufio.NewScanner(file)
	products := make([]Product, 0, 100)

	for scanner.Scan() {
		var rawProduct map[string]interface{}
		if err := json.Unmarshal(scanner.Bytes(), &rawProduct); err != nil {
			log.Printf("Falha ao fazer unmarshal do produto: %v", err.Error())
			history.IncrementFailedImports()
			continue
		}

		importedNow := time.Now().Unix()

		rawCode := getString(rawProduct, "code")
		if rawCode == nil {
			log.Printf("Produto ignorado: motivo=SEM_CODIGO | raw=%+v", rawProduct)
			history.IncrementFailedImports()
			continue
		}

		productName := getString(rawProduct, "product_name")

		if productName == nil {
			log.Printf("Produto ignorado: motivo=SEM_NOME | code=%s | principais_campos={brands:%v, categories:%v, quantity:%v}", *rawCode, getString(rawProduct, "brands"), getString(rawProduct, "categories"), getString(rawProduct, "quantity"))
			history.IncrementFailedImports()
			continue
		}

		servingQuantity := getFloat64(rawProduct, "serving_quantity")
		realServingQuantity := 0.0
		if servingQuantity != nil {
			realServingQuantity = *servingQuantity
		}

		nutriscoreScore := getInt64(rawProduct, "nutrition-score-fr_100g")
		realNutriscoreScore := int64(0)
		if nutriscoreScore != nil {
			realNutriscoreScore = *nutriscoreScore
		}

		nutriscoreGrade := getString(rawProduct, "nutriscore_grade_fr")
		realNutriscoreGrade := ""
		if nutriscoreGrade != nil {
			realNutriscoreGrade = *nutriscoreGrade
		}

		product := Product{
			Code:            normalizeCode(*rawCode),
			Url:             getString(rawProduct, "url"),
			ProductName:     *productName,
			Brands:          getString(rawProduct, "brands"),
			Labels:          getString(rawProduct, "labels"),
			Cities:          getString(rawProduct, "cities"),
			PurchasePlaces:  getString(rawProduct, "purchase_places"),
			Stores:          getString(rawProduct, "stores"),
			Categories:      getString(rawProduct, "categories"),
			Creator:         getString(rawProduct, "creator"),
			CreatedT:        getInt64(rawProduct, "created_t"),
			LastModifiedT:   getInt64(rawProduct, "last_modified_t"),
			Quantity:        getString(rawProduct, "quantity"),
			ServingSize:     getString(rawProduct, "serving_size"),
			IngredientsText: getString(rawProduct, "ingredients_text"),
			Traces:          getString(rawProduct, "traces"),
			ServingQuantity: realServingQuantity,
			NutriscoreScore: realNutriscoreScore,
			NutriscoreGrade: &realNutriscoreGrade,
			MainCategory:    getString(rawProduct, "main_category"),
			ImageUrl:        getString(rawProduct, "image_url"),
			ImportedT:       &importedNow,
			Status:          ProductStatusPublished,
		}

		importedNow = time.Now().Unix()
		product.ImportedT = &importedNow
		products = append(products, product)

		if len(products) >= 100 {
			if err := saveProducts(products, collection, history, historyCollection); err != nil {
				history.IncrementFailedImports()
			}
			products = products[:0]
		}
	}

	if len(products) > 0 {
		if err := saveProducts(products, collection, history, historyCollection); err != nil {
			history.IncrementFailedImports()
		}
	}

	history.IncrementProcessedFiles()
	return scanner.Err()
}

func getString(m map[string]interface{}, key string) *string {
	if val, ok := m[key]; ok {
		if str, ok := val.(string); ok {
			return &str
		}
	}
	return nil
}

func getFloat64(m map[string]interface{}, key string) *float64 {
	if val, ok := m[key]; ok {
		switch v := val.(type) {
		case float64:
			return &v
		}
	}
	return nil
}

func getInt64(m map[string]interface{}, key string) *int64 {
	if val, ok := m[key]; ok {
		switch v := val.(type) {
		case float64:
			i := int64(v)
			return &i
		case int64:
			return &v
		case int:
			i := int64(v)
			return &i
		case string:
			if i, err := strconv.ParseInt(v, 10, 64); err == nil {
				return &i
			}
		}
	}
	return nil
}

func createImportHistory(collection *mongo.Collection) (*ImportHistory, error) {
	now := time.Now()
	history := &ImportHistory{
		InitTime:          now,
		Status:            ImportStatusRunning,
		TotalFiles:        0,
		ProcessedFiles:    0,
		TotalProducts:     0,
		SuccessfulImports: 0,
		FailedImports:     0,
	}

	result, err := collection.InsertOne(context.TODO(), history)
	if err != nil {
		return nil, err
	}

	history.ID = result.InsertedID.(primitive.ObjectID)
	return history, nil
}

func updateImportHistory(collection *mongo.Collection, history *ImportHistory) error {
	update := bson.M{
		"$set": bson.M{
			"processed_files":    history.ProcessedFiles,
			"total_products":     history.TotalProducts,
			"successful_imports": history.SuccessfulImports,
			"failed_imports":     history.FailedImports,
			"skipped_imports":    history.SkippedImports,
			"total_files":        history.TotalFiles,
		},
	}

	_, err := collection.UpdateOne(
		context.TODO(),
		bson.M{"_id": history.ID},
		update,
	)
	return err
}

func finalizeImportHistory(collection *mongo.Collection, history *ImportHistory, status ImportStatus, errMsg *string) error {
	now := time.Now()
	update := bson.M{
		"$set": bson.M{
			"end_time":           now,
			"status":             status,
			"error_message":      errMsg,
			"processed_files":    history.ProcessedFiles,
			"total_products":     history.TotalProducts,
			"successful_imports": history.SuccessfulImports,
			"failed_imports":     history.FailedImports,
			"skipped_imports":    history.SkippedImports,
			"total_files":        history.TotalFiles,
		},
	}

	_, err := collection.UpdateOne(
		context.TODO(),
		bson.M{"_id": history.ID},
		update,
	)
	return err
}

func main() {
	log.Printf("Iniciando importação do Open Food Facts...")
	log.Printf("Configurações carregadas:")
	log.Printf("- MongoDB URI: %s", mongoURI)
	log.Printf("- Database: %s", mongoDatabase)
	log.Printf("- Collection: %s", mongoCollection)
	log.Printf("- Workers de download: %d", downloadWorkers)
	log.Printf("- Workers de processamento: %d", processWorkers)
	log.Printf("- Produtos por arquivo: %d", productsPerFile)
	if importTimeLimit > 0 {
		log.Printf("- Limite de tempo de importação: %d minutos", importTimeLimit)
	}

	startTime := time.Now()

	// Configurar contexto com timeout se necessário
	ctx := context.Background()
	var cancel context.CancelFunc
	if importTimeLimit > 0 {
		ctx, cancel = context.WithTimeout(context.Background(), time.Duration(importTimeLimit)*time.Minute)
		defer cancel()
	}

	if err := os.MkdirAll(storagePath, 0755); err != nil {
		log.Fatal("Erro ao criar diretório de armazenamento:", err)
	}
	log.Printf("Diretório de armazenamento verificado: %s", storagePath)

	log.Printf("Conectando ao MongoDB...")
	client, err := mongo.Connect(ctx, options.Client().ApplyURI(mongoURI))
	if err != nil {
		log.Fatal("Erro ao conectar ao MongoDB:", err)
	}

	// Testar conexão com timeout de 5 segundos
	ctxPing, cancelPing := context.WithTimeout(ctx, 5*time.Second)
	defer cancelPing()
	err = client.Ping(ctxPing, nil)
	if err != nil {
		log.Fatal("Não foi possível conectar ao MongoDB:", err)
	}

	defer client.Disconnect(ctx)
	log.Printf("Conexão com MongoDB estabelecida")

	collection := client.Database(mongoDatabase).Collection(mongoCollection)
	historyCollection := client.Database(mongoDatabase).Collection("import_history")

	// Criar registro inicial do histórico
	history, err := createImportHistory(historyCollection)
	if err != nil {
		log.Fatal("Erro ao criar histórico de importação:", err)
	}

	// Canal para monitorar o timeout
	done := make(chan struct{})
	go func() {
		select {
		case <-ctx.Done():
			if ctx.Err() == context.DeadlineExceeded {
				log.Printf("Limite de tempo de importação atingido (%d minutos)", importTimeLimit)
				errMsg := fmt.Sprintf("Importação interrompida: limite de tempo de %d minutos atingido", importTimeLimit)
				if err := finalizeImportHistory(historyCollection, history, ImportStatusFailed, &errMsg); err != nil {
					log.Printf("Erro ao finalizar histórico: %v", err)
				}
				os.Exit(1)
			}
		case <-done:
			return
		}
	}()

	log.Printf("Obtendo lista de arquivos do Open Food Facts...")
	resp, err := http.Get(indexURL)
	if err != nil {
		errMsg := fmt.Sprintf("Erro ao obter lista de arquivos: %v", err)
		finalizeImportHistory(historyCollection, history, ImportStatusFailed, &errMsg)
		log.Fatal(errMsg)
	}
	defer resp.Body.Close()

	scanner := bufio.NewScanner(resp.Body)
	var files []string
	for scanner.Scan() {
		if filename := strings.TrimSpace(scanner.Text()); filename != "" {
			files = append(files, filename)
		}
	}
	log.Printf("Total de arquivos encontrados: %d", len(files))

	history.TotalFiles = len(files)
	if err := updateImportHistory(historyCollection, history); err != nil {
		log.Printf("Erro ao atualizar histórico: %v", err)
	}

	downloadChan := make(chan string, len(files))
	extractChan := make(chan string, len(files))
	var wgDownload sync.WaitGroup
	var wgProcess sync.WaitGroup

	log.Printf("Iniciando workers de download...")
	for i := 0; i < downloadWorkers; i++ {
		wgDownload.Add(1)
		go func(workerID int) {
			defer wgDownload.Done()
			for filename := range downloadChan {
				log.Printf("[Worker %d] Processando download: %s", workerID, filename)
				filePath := filepath.Join(storagePath, filename)
				if _, err := os.Stat(filePath); os.IsNotExist(err) {
					if err := downloadFile(baseURL+filename, filePath); err != nil {
						log.Printf("[Worker %d] Erro ao baixar %s: %v", workerID, filename, err)
						continue
					}
					log.Printf("[Worker %d] Download concluído: %s", workerID, filename)
				} else {
					log.Printf("[Worker %d] Arquivo já existe: %s", workerID, filename)
				}
				extractChan <- filename
			}
		}(i)
	}

	log.Printf("Iniciando workers de extração e processamento...")
	for i := 0; i < processWorkers; i++ {
		wgProcess.Add(1)
		go func(workerID int) {
			defer wgProcess.Done()
			for filename := range extractChan {
				log.Printf("[Worker %d] Processando arquivo: %s", workerID, filename)
				filePath := filepath.Join(storagePath, filename)
				extractedPath := filepath.Join(storagePath, strings.TrimSuffix(filename, ".gz"))

				if _, err := os.Stat(extractedPath); os.IsNotExist(err) {
					log.Printf("[Worker %d] Extraindo arquivo: %s", workerID, filename)
					if err := extractGzip(filePath, extractedPath); err != nil {
						log.Printf("[Worker %d] Erro ao extrair %s: %v", workerID, filename, err)
						continue
					}
					log.Printf("[Worker %d] Extração concluída: %s", workerID, filename)
				} else {
					log.Printf("[Worker %d] Arquivo já extraído: %s", workerID, filename)
				}

				log.Printf("[Worker %d] Processando produtos do arquivo: %s", workerID, filename)
				if err := processFile(filename, collection, history, historyCollection); err != nil {
					log.Printf("[Worker %d] Erro ao processar %s: %v", workerID, filename, err)
				}
				log.Printf("[Worker %d] Processamento concluído: %s", workerID, filename)
			}
		}(i)
	}

	log.Printf("Iniciando processamento dos arquivos...")
	for _, filename := range files {
		downloadChan <- filename
	}
	close(downloadChan)

	log.Printf("Aguardando conclusão dos downloads...")
	wgDownload.Wait()
	close(extractChan)

	log.Printf("Aguardando conclusão das extrações e processamentos...")
	wgProcess.Wait()

	close(done)
	duration := time.Since(startTime)

	if err := finalizeImportHistory(historyCollection, history, ImportStatusCompleted, nil); err != nil {
		log.Printf("Erro ao finalizar histórico: %v", err)
	}

	log.Printf("=== Importação concluída! ===")
	log.Printf("Tempo total de execução: %v", duration)
	log.Printf("Total de arquivos: %d", history.TotalFiles)
	log.Printf("Arquivos processados: %d", history.ProcessedFiles)
	log.Printf("Total de produtos: %d", history.TotalProducts)
	log.Printf("Importações bem-sucedidas: %d", history.SuccessfulImports)
	log.Printf("Importações com falha: %d", history.FailedImports)
	log.Printf("Importações ignoradas: %d", history.SkippedImports)
	log.Printf("Taxa de sucesso: %.2f%%", float64(history.SuccessfulImports)/float64(history.TotalProducts)*100)
	log.Printf("Taxa de ignorados: %.2f%%", float64(history.SkippedImports)/float64(history.TotalProducts)*100)
}
