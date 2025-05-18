#!/bin/bash

cd $(dirname $0)

# Criar diretório de build se não existir
mkdir -p build

# Inicializar módulo Go se não existir
test -f go.mod || go mod init truckpag-product-parser

go mod tidy

# Compilar o arquivo Go
go build -o build/import_openfoodfacts import_openfoodfacts/main.go

# Tornar o executável executável
chmod +x build/import_openfoodfacts

echo "Build concluído com sucesso!"
