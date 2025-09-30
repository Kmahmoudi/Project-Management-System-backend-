# Project Management System (Backend) – Skill Test

Laravel backend implementing:
- Authentication (Sanctum)
- Projects / Tasks / Comments
- Role-based access (admin, manager, user)
- Middleware (request logging)
- Services (TaskAssignmentService)
- Queued notifications (TaskAssigned)
- Caching for project listings
- Factories, Seeders, Feature & Unit tests

## Requirements
- PHP 8.2+
- MySQL 8+ (or MariaDB compatible)
- Composer
- Node (optional)
- Redis or File cache (file is fine)
- Mailhog / SMTP (for local mail testing)

## Setup
```bash
composer install

# Configure .env
cp .env.example .env
php artisan key:generate
# Set DB_*, QUEUE_CONNECTION=database (or redis), CACHE_DRIVER=file (or redis)
# Set MAIL_* (Mailhog recommended)

# Sanctum & queue tables
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force
php artisan queue:table
php artisan migrate

# Seed sample data (3 admins, 3 managers, 5 users, 5 projects, 10 tasks, 10 comments)
php artisan db:seed


###################################### SOME MANUAL TESTS ####################################
$ curl -X POST http://localhost:8000/api/register   -H "Content-Type: application/json"   -d '{"name":"John","email":"john@example.com","password":"password123","password_confirmation":"password123"}'

{"success":true,"data":{"user":{"name":"John","email":"john@example.com","phone":null,"role":"user","updated_at":"2025-09-30T17:07:07.000000Z","created_at":"2025-09-30T17:07:07.000000Z","id":1},"token":"1|uGSGJ
FAYitTghxxLGITt9H1eHstNaBrpFr8POJhw"},"message":"Registered"}


$ curl -H "Accept: application/json" \
     -H "Authorization: Bearer 1|uGSGJFAYitTghxxLGITt9H1eHstNaBrpFr8POJhw" \
     http://localhost:8000/api/me

{"success":true,"data":{"id":1,"name":"John","email":"john@example.com","phone":null,"role":"user","email_verified_at":null,"created_at":"2025-09-30T17:07:07.000000Z","updated_at":"2025-09-30T17:07:07.000000Z"}
,"message":"Me"}

$ curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Admin","email":"admin@example.com","password":"password123","password_confirmation":"password123","role":"admin"}'

{"success":true,"data":{"user":{"name":"Admin","email":"admin@example.com","phone":null,"role":"admin","updated_at":"2025-09-30T17:30:04.000000Z","created_at":"2025-09-30T17:30:04.000000Z","id":2},"token":"2|hw
N0UjPtMlxpxVfIUw4WL0uSQUr7GrzBudkDQ1RS"},"message":"Registered"}

$ curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Manager","email":"mgr@example.com","password":"password123","password_confirmation":"password123","role":"manager"}'

{"success":true,"data":{"user":{"name":"Manager","email":"mgr@example.com","phone":null,"role":"manager","updated_at":"2025-09-30T17:50:32.000000Z","created_at":"2025-09-30T17:50:32.000000Z","id":3},"token":"3|
NQyGrbg9gDDkY8utl4mE2xd1YuJHNm300PLBjH2E"},"message":"Registered"}


curl -X POST http://localhost:8000/api/projects \
  -H "Authorization: Bearer 2|hwN0UjPtMlxpxVfIUw4WL0uSQUr7GrzBudkDQ1RS" \
  -H "Content-Type: application/json" \
  -d '{"title":"Project A","description":"Demo"}'
{"success":true,"data":{"title":"Project A","description":"Demo","created_by":2,"updated_at":"2025-09-30T17:31:31.000000Z","created_at":"2025-09-30T17:31:31.000000Z","id":1},"message":"Project created"}


curl -X POST http://localhost:8000/api/projects/1/tasks \
  -H "Authorization: Bearer 3|NQyGrbg9gDDkY8utl4mE2xd1YuJHNm300PLBjH2E" \
  -H "Content-Type: application/json" \
  -d '{"title":"T1","description":"D1","assigned_to": 1 }'
  
  {"success":true,"data":{"title":"T1","description":"D1","status":"pending","due_date":null,"project_id":1,"assigned_to":1,"updated_at":"2025-09-30T17:53:03.000000Z","created_at":"2025-09-30T17:53:03.000000Z","i
d":1,"assignee":{"id":1,"name":"John","email":"john@example.com","phone":null,"role":"user","email_verified_at":null,"created_at":"2025-09-30T17:07:07.000000Z","updated_at":"2025-09-30T17:07:07.000000Z"}},"mess
age":"Task created"}

curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer 1|uGSGJFAYitTghxxLGITt9H1eHstNaBrpFr8POJhw" \
  -H "Content-Type: application/json" \
  -d '{"status":"in-progress"}'

{"success":true,"data":{"id":1,"title":"T1","description":"D1","status":"in-progress","due_date":null,"project_id":1,"assigned_to":1,"created_at":"2025-09-30T17:53:03.000000Z","updated_at":"2025-09-30T17:54:52.
000000Z","assignee":{"id":1,"name":"John","email":"john@example.com","phone":null,"role":"user","email_verified_at":null,"created_at":"2025-09-30T17:07:07.000000Z","updated_at":"2025-09-30T17:07:07.000000Z"}},"
message":"Task updated"}

curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer 1|uGSGJFAYitTghxxLGITt9H1eHstNaBrpFr8POJhw" \
  -H "Content-Type: application/json" \
  -d '{"assigned_to": 2 }'
  
{"success":false,"data":null,"message":"Only managers can reassign tasks"}

curl -X POST http://localhost:8000/api/tasks/1/comments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|uGSGJFAYitTghxxLGITt9H1eHstNaBrpFr8POJhw" \
  -H "Content-Type: application/json" \
  -d '{"body":"First comment!"}'
  
  {"success":true,"data":{"body":"First comment!","task_id":1,"user_id":1,"updated_at":"2025-09-30T18:04:26.000000Z","created_at":"2025-09-30T18:04:26.000000Z","id":1,"user":{"id":1,"name":"John"}},"message":"Com
ment added"}

curl http://localhost:8000/api/tasks/1/comments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|uGSGJFAYitTghxxLGITt9H1eHstNaBrpFr8POJhw" \

{"success":true,"data":{"current_page":1,"data":[{"id":1,"body":"First comment!","task_id":1,"user_id":1,"created_at":"2025-09-30T18:04:26.000000Z","updated_at":"2025-09-30T18:04:26.000000Z","user":{"id":1,"nam
e":"John"}}],"first_page_url":"http:\/\/localhost:8000\/api\/tasks\/1\/comments?page=1","from":1,"last_page":1,"last_page_url":"http:\/\/localhost:8000\/api\/tasks\/1\/comments?page=1","links":[{"url":null,"lab
el":"&laquo; Previous","active":false},{"url":"http:\/\/localhost:8000\/api\/tasks\/1\/comments?page=1","label":"1","active":true},{"url":null,"label":"Next &raquo;","active":false}],"next_page_url":null,"path"
:"http:\/\/localhost:8000\/api\/tasks\/1\/comments","per_page":10,"prev_page_url":null,"to":1,"total":1},"message":"Comments list"}


