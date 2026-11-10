# PROJECT: BLACKOUT — TROUBLESHOOTING & FAQ

## Common Issues & Resolutions

### 1. Port 80 or Port 8000 Already in Use
**Symptoms:** `docker compose` fails with `bind: address already in use`.  
**Resolution:**
Check active services on port 80 / 8000:
```bash
sudo lsof -i :80
sudo lsof -i :8000
```
Stop default Apache or Nginx services if running on the host:
```bash
sudo systemctl stop apache2
sudo systemctl stop nginx
```
Or edit the host port binding in `docker-compose.yml` (e.g. `"8080:80"`).

### 2. File Upload Permissions Error (H05)
**Symptoms:** `Failed to write uploaded file to staging store` when uploading attachment.  
**Resolution:**
Ensure the upload directory has write permissions inside the container or host mount:
```bash
docker exec -it blackout-target-app chown -R www-data:www-data /var/www/html/public/uploads
docker exec -it blackout-target-app chmod -R 775 /var/www/html/public/uploads
```

### 3. SSRF Internal Microservice Not Reachable (A01)
**Symptoms:** Probe returns connection refused when querying `mock-internal:8080`.  
**Resolution:**
Verify the `blackout-mock-internal` container is running:
```bash
docker ps --filter name=mock-internal
docker logs blackout-mock-internal
```
Ensure both `target-app` and `mock-internal` share the `target-internal-net` Docker network. Note that the fallback mock generator in `fetch.php` ensures seamless test execution even if the standalone microservice is stopped.

### 4. Database Initialization Incomplete
**Symptoms:** Database errors on first startup.  
**Resolution:**
If the database was interrupted during first boot, persistent volumes may contain partial state:
```bash
docker compose down -v
docker compose up -d --build
```
This forces a clean re-initialization of `schema.sql` and `seed.sql`.

### 5. Resetting a Participant's State for A04 (Race Condition)
If a participant fails the race condition and uses up their bonus, they can call:
```
http://TARGET-IP/api/rewards.php?action=reset
```
This resets their balance to 10 credits and clears the claimed coupon flag.
