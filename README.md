
# Aufgabenmanagement-API
Eine RESTful API zur Verwaltung von Aufgaben (Tasks) mit CRUD-Operationen.  
Die API wurde mit **Symfony** entwickelt und verwendet **SQLite** als Datenbank.

## Installation und Ausführung der API
### Voraussetzungen:
- PHP >= 8.1
- Composer
- SQLite

### Installationsanweisungen:
1. Repository klonen:
   ```bash
   git clone <REPOSITORY_URL>
   cd <PROJECT_DIRECTORY>
   ```

2. Abhängigkeiten installieren:
   ```bash
   composer install
   ```

3. SQLite-Datenbank erstellen:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

4. Symfony Server starten:
   ```bash
   symfony serve
   ```

5. API testen unter:
   ```
   http://127.0.0.1:8000
   ```

## Endpunkte der API
### 1. Task erstellen
**Methode:** `POST`  
**URL:** `/tasks`  
**Beschreibung:** Erstellt eine neue Aufgabe.

**Request Body (JSON):**
```json
{
    "title": "Neue Aufgabe",
    "description": "Das ist eine Beispielbeschreibung",
    "status": "pending",
    "dueDate": "2024-12-31T23:59:59"
}
```

**Response (201 Created):**
```json
{
    "id": 1,
    "title": "Neue Aufgabe",
    "description": "Das ist eine Beispielbeschreibung",
    "status": "pending",
    "dueDate": "2024-12-31T23:59:59",
    "createdAt": "2024-06-16T10:30:00",
    "updatedAt": "2024-06-16T10:30:00"
}
```

**Fehlerfälle:**
- Fehlende Felder:  
  **400 Bad Request**
  ```json
  {
      "error": "Validation failed",
      "details": [
          { "field": "title", "message": "This value should not be blank." }
      ]
  }
  ```

### 2. Alle Tasks abrufen
**Methode:** `GET`  
**URL:** `/tasks`  
**Beschreibung:** Gibt eine Liste aller Aufgaben zurück. Optional kann die Antwort paginiert und nach `status` gefiltert werden.

**Optionale Query-Parameter:**
| Parameter   | Typ      | Beschreibung                        | Beispiel            |
|-------------|----------|-------------------------------------|---------------------|
| `page`      | integer  | Seite für Pagination (1-basiert)    | `/tasks?page=2`     |
| `limit`     | integer  | Anzahl der Einträge pro Seite       | `/tasks?limit=10`   |
| `status`    | string   | Filter nach Task-Status             | `/tasks?status=pending` |

**Response ohne Parameter (alle Tasks):**
```json
[
    {
        "id": 1,
        "title": "Neue Aufgabe",
        "description": "Das ist eine Beispielbeschreibung",
        "status": "pending",
        "dueDate": "2024-12-31T23:59:59",
        "createdAt": "2024-06-16T10:30:00",
        "updatedAt": "2024-06-16T10:30:00"
    }
]
```

**Response mit Pagination und Status-Filter:**
```json
{
    "data": [
        {
            "id": 2,
            "title": "Neue Aufgabe 2",
            "status": "completed",
            "createdAt": "2024-06-16T11:00:00"
        }
    ],
    "pagination": {
        "total": 15,
        "current_page": 2,
        "limit": 5,
        "total_pages": 3
    }
}
```

**Fehlerfälle:**
- Ungültiger Status:  
  **400 Bad Request**
  ```json
  {
      "error": "Invalid status filter",
      "valid_statuses": ["pending", "in_progress", "completed"]
  }
  ```

### 3. Einzelnen Task abrufen
**Methode:** `GET`  
**URL:** `/tasks/{id}`  
**Beschreibung:** Ruft eine spezifische Aufgabe anhand der ID ab.

**Response (200 OK):**
```json
{
    "id": 1,
    "title": "Neue Aufgabe",
    "description": "Das ist eine Beispielbeschreibung",
    "status": "pending",
    "dueDate": "2024-12-31T23:59:59",
    "createdAt": "2024-06-16T10:30:00",
    "updatedAt": "2024-06-16T10:30:00"
}
```

**Fehlerfälle:**
- **404 Not Found**
  ```json
  {
      "error": "Task not found"
  }
  ```

### 4. Task aktualisieren
**Methode:** `PUT`  
**URL:** `/tasks/{id}`  
**Beschreibung:** Aktualisiert eine bestehende Aufgabe.

**Request Body (JSON):**
```json
{
    "status": "completed",
    "description": "Beschreibung aktualisiert"
}
```

**Response (200 OK):**
```json
{
    "id": 1,
    "title": "Neue Aufgabe",
    "description": "Beschreibung aktualisiert",
    "status": "completed",
    "dueDate": "2024-12-31T23:59:59",
    "createdAt": "2024-06-16T10:30:00",
    "updatedAt": "2024-06-16T11:00:00"
}
```

**Fehlerfälle:**
- Ungültige ID: **404 Not Found**
- Validierungsfehler: **400 Bad Request**

### 5. Task löschen
**Methode:** `DELETE`  
**URL:** `/tasks/{id}`  
**Beschreibung:** Löscht eine Aufgabe anhand der ID.

**Response (200 OK):**
```json
{
    "message": "Task deleted successfully"
}
```

**Fehlerfälle:**
- Ungültige ID: **404 Not Found**

## API-Endpunkte testen mit cURL

### 1. Task erstellen
**Methode:** `POST`  
**URL:** `/tasks`  

```bash
curl -X POST http://127.0.0.1:8000/tasks \
     -H "Content-Type: application/json" \
     -d '{
           "title": "Neue Aufgabe",
           "description": "Das ist eine Beispielbeschreibung",
           "status": "pending",
           "dueDate": "2024-12-31T23:59:59"
         }'
```

### 2. Alle Tasks abrufen (mit Filter und Pagination)
```bash
# Alle Tasks ohne Filter
curl -X GET "http://127.0.0.1:8000/tasks"

# Tasks gefiltert nach Status
curl -X GET "http://127.0.0.1:8000/tasks?status=pending"

# Tasks mit Pagination
curl -X GET "http://127.0.0.1:8000/tasks?page=2&limit=5"
```

### 3. Einzelnen Task abrufen
**Methode:** `GET`  
**URL:** `/tasks/{id}`  

```bash
curl -X GET http://127.0.0.1:8000/tasks/1
```

### 4. Task aktualisieren
**Methode:** `PUT`  
**URL:** `/tasks/{id}`  

```bash
curl -X PUT http://127.0.0.1:8000/tasks/1 \
     -H "Content-Type: application/json" \
     -d '{
           "status": "completed",
           "description": "Beschreibung aktualisiert"
         }'
```

### 5. Task löschen
**Methode:** `DELETE`  
**URL:** `/tasks/{id}`  

```bash
curl -X DELETE http://127.0.0.1:8000/tasks/1
```

## Lizenz

Dieses Projekt steht unter der [MIT-Lizenz](https://opensource.org/licenses/MIT).
