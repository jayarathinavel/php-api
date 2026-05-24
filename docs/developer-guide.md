# Developer Guide

This API contains normal auth/user routes plus a generic CRUD layer for simple frontend apps. The CRUD layer is metadata-driven: to add a new app feature, create the database table and register it in `crud_resources`. No PHP code change is needed for each feature.

## Authentication

Register and login first:

```http
POST /register
POST /login
```

Protected routes require:

```http
Authorization: Bearer <token>
```

For old `/users` routes, also send:

```http
X-App-Id: <app_id>
```

For generic CRUD routes, the app id is read from the URL:

```http
/{appId}/{featureName}/...
```

## Generic CRUD Setup

Create a feature table. Example:

```sql
CREATE TABLE `myApp_alpha` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

Register the table:

```sql
INSERT INTO crud_resources (app_id, feature_name, table_name, primary_key)
VALUES ('myApp', 'alpha', 'myApp_alpha', 'id');
```

Now the feature is available through generic CRUD routes.

## Admin SQL Endpoint

Admins can run one DDL or DML statement through:

```http
POST /admin/query
Content-Type: text/plain
Authorization: Bearer <admin-token>
X-App-Id: <app_id>

-- api.myApp_alpha definition

CREATE TABLE `myApp_alpha` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```

JSON with parameters is also supported when needed:

```http
POST /admin/query
Content-Type: application/json
Authorization: Bearer <admin-token>
X-App-Id: <app_id>

{
  "query": "INSERT INTO sample_table (name) VALUES (?)",
  "params": ["Example"]
}
```

Only these statement types are accepted: `CREATE`, `ALTER`, `DROP`, `TRUNCATE`, `RENAME`, `INSERT`, `UPDATE`, `DELETE`, and `REPLACE`. Multiple statements in one request are rejected.

## Generic CRUD Routes

```http
GET    /{appId}/{featureName}/all
GET    /{appId}/{featureName}/{id}
POST   /{appId}/{featureName}
POST   /{appId}/{featureName}/all
PUT    /{appId}/{featureName}/{id}
PATCH  /{appId}/{featureName}/{id}
DELETE /{appId}/{featureName}/{id}
```

Example:

```http
GET /myApp/alpha/all
```

Create one record:

```http
POST /myApp/alpha
Content-Type: application/json
Authorization: Bearer <token>

{
  "name": "First record",
  "description": "Created from frontend"
}
```

Create multiple records:

```http
POST /myApp/alpha/all
Content-Type: application/json
Authorization: Bearer <token>

[
  {
    "name": "First record",
    "description": "Created from frontend"
  },
  {
    "name": "Second record",
    "description": "Created from frontend"
  }
]
```

Update a record:

```http
PATCH /myApp/alpha/1
Content-Type: application/json
Authorization: Bearer <token>

{
  "description": "Updated value"
}
```

Delete a record:

```http
DELETE /myApp/alpha/1
Authorization: Bearer <token>
```

## Validation Rules

Request bodies are checked against the actual target table schema.

The API rejects:

- unknown columns
- missing required columns on create
- `null` values for non-null columns
- wrong primitive types for common string, integer, and number columns
- strings longer than the database column length
- invalid bulk payloads

The primary key and auto-increment columns cannot be written from request bodies.

## Postman

Import these files:

- `docs/postman_collection.json`
- `docs/postman_environment.json`

Set these environment values:

- `base_url`
- `app_id`
- `feature_name`
- `token`
- `record_id`

Use the login request first. The collection saves `token` automatically when login succeeds, and the generic create request saves `record_id` when the response includes `data.id`.
