CREATE TABLE IF NOT EXISTS "categories" (
	"id" INTEGER,
	"name" VARCHAR NOT NULL,
	PRIMARY KEY("id")
);

CREATE TABLE IF NOT EXISTS "brands" (
	"id" INTEGER,
	"name" VARCHAR NOT NULL,
	PRIMARY KEY("id")
);

CREATE TABLE IF NOT EXISTS "products" (
	"id" INTEGER,
	"category_id" INTEGER,
	"brand_id" INTEGER,
	"title" VARCHAR NOT NULL,
	"price" BLOB NOT NULL,
	PRIMARY KEY("id"),
	FOREIGN KEY ("category_id") REFERENCES "categories"("id")
	ON UPDATE NO ACTION ON DELETE NO ACTION,
	FOREIGN KEY ("brand_id") REFERENCES "brands"("id")
	ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE IF NOT EXISTS "product_images" (
	"id" INTEGER,
	"product_id" INTEGER,
	"image_url" TEXT,
	PRIMARY KEY("id"),
	FOREIGN KEY ("product_id") REFERENCES "products"("id")
	ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE IF NOT EXISTS "product_variants" (
	"id" INTEGER,
	"product_id" INTEGER,
	"size" VARCHAR,
	"color" VARCHAR,
	"stock" INTEGER,
	PRIMARY KEY("id"),
	FOREIGN KEY ("product_id") REFERENCES "products"("id")
	ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE TABLE IF NOT EXISTS "orders" (
	"id" INTEGER,
	"total_amount" BLOB,
	"status" VARCHAR,
	PRIMARY KEY("id")
);

CREATE TABLE IF NOT EXISTS "order_items" (
	"id" INTEGER,
	"order_id" INTEGER,
	"product_variant_id" INTEGER,
	"quantity" INTEGER,
	"price" BLOB,
	PRIMARY KEY("id"),
	FOREIGN KEY ("order_id") REFERENCES "orders"("id")
	ON UPDATE NO ACTION ON DELETE NO ACTION,
	FOREIGN KEY ("product_variant_id") REFERENCES "product_variants"("id")
	ON UPDATE NO ACTION ON DELETE NO ACTION
);
