DROP TABLE IF EXISTS "clients";
CREATE TABLE IF NOT EXISTS "clients" (
	"id" UUID NOT NULL DEFAULT gen_random_uuid(),
	"first_name" VARCHAR(32) NOT NULL,
	"last_name" VARCHAR(32) NOT NULL,
	"email" TEXT NOT NULL,
	"phone" VARCHAR(16) NOT NULL,
	"created_at" TIMESTAMP NOT NULL DEFAULT now(),
	"updated_at" TIMESTAMP NOT NULL DEFAULT now(),
	"deleted_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "applications";
CREATE TABLE IF NOT EXISTS "applications" (
	"id" UUID NOT NULL DEFAULT gen_random_uuid(),
	"client_id" UUID NOT NULL,
	"term" SMALLINT NOT NULL,
	"amount" NUMERIC(10,2) NOT NULL,
	"currency" CHAR(3) NOT NULL,
	"created_at" TIMESTAMP NOT NULL DEFAULT now(),
	"updated_at" TIMESTAMP NOT NULL DEFAULT now(),
	"deleted_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "FK__clients" FOREIGN KEY ("client_id") REFERENCES "clients" ("id")  ON DELETE RESTRICT
);
