db = db.getSiblingDB("product_parser");

db.createCollection("products");
db.createCollection("import_history");

db.products.createIndex({ code: 1 }, { unique: true });
