# PROJECT: BLACKOUT — DEPLOYMENT & OPERATION MANUAL

## Architecture Overview
PROJECT: BLACKOUT is an isolated web penetration testing environment:
- **Target Environment (E CORP):** Runs on Port 80 (`http://TARGET-IP/`).
  - Root `/` automatically serves `target-site/public/index.html`.
  - Connected to isolated database container `blackout-target-db` and private internal microservice `blackout-mock-internal`.
  - Challenge secrets and central flag registry are secured outside the public web root in `target-site/challenge-secrets/`.

---

## Step-by-Step Deployment Guide

### Step 1 & 2: Install Docker & Docker Compose
Follow instructions in [installation.md](file:///installation.md).

### Step 3: Clone or Copy Project Files
Place the project repository on the server:
```bash
cd /opt/
sudo git clone <repository-url> project-blackout
cd project-blackout
```

### Step 4 & 5: Configure & Initialize Database
The database schema and synthetic seed records initialize automatically on first startup via Docker entrypoint scripts located in:
- `target-site/database/schema.sql` & `seed.sql`

### Step 6: Start the Target Website
Launch the target environment via Docker Compose:
```bash
docker compose up -d --build
```
Or from the `target-site/` directory:
```bash
cd target-site && docker compose up -d --build
```

### Step 7: Find Target IP
Determine the server's LAN / WAN IP address:
```bash
ip -4 addr show scope global | grep -oP '(?<=inet\s)\d+(\.\d+){3}'
```
Example Output: `192.168.1.150`
- Target Website: `http://192.168.1.150/`

### Step 8: Verify Target URL
Test from the host or another machine on the LAN:
```bash
# Verify Target Website loads index.html
curl -I http://192.168.1.150/
```

### Step 9: Test Participant Connectivity
Ensure the firewall allows traffic on port 80:
```bash
sudo ufw allow 80/tcp
sudo ufw reload
```

### Step 10: Resetting the Environment
To perform a complete reset between events:
```bash
# Teardown containers and remove persistent volumes
docker compose down -v

# Re-launch with fresh database state
docker compose up -d --build
```
