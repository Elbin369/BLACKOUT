# PROJECT: BLACKOUT

Web exploitation CTF target environment for the fictional E CORP corporate website.

"Every system has a weakness. Find yours."

## Overview
PROJECT: BLACKOUT is a deliberately vulnerable web application designed for realistic CTF-style exploitation practice. The challenge environment simulates a corporate website with public pages, private APIs, hidden directories, legacy portals, and an internal SSRF-only service.

The repository includes:
- a vulnerable frontend for E CORP
- a local Python runner for quick testing
- a Docker Compose setup for multi-container deployment
- 20 intentionally planted vulnerabilities across Easy, Medium, Hard, and Advanced tiers

## Challenge summary
- Total challenges: 20
- Difficulty split: 3 Easy, 7 Medium, 5 Hard, 5 Advanced
- Total points pool: 3,350
- Flag format: `Flagfound{...}`
- Entry point: the site loads `target-site/public/index.html` by default

## Repository layout
```text
BLACKOUT/
├── README.md
├── docker-compose.yml                # Root deployment orchestrator
├── render.yaml                       # Render hosting config
├── requirements.txt                  # Python dependencies
├── start_local.py                    # Local runner for the vulnerable target
├── target-site/
│   ├── Dockerfile                    # Target app container
│   ├── docker-compose.yml            # Inner target setup
│   ├── public/                       # Browser-facing web root
│   │   ├── index.html                # Main landing page / terminal UI
│   │   ├── about.html                # Corporate overview
│   │   ├── services.html             # Pricing / service tier pages
│   │   ├── news.html                 # Search and intelligence content
│   │   ├── documents.html            # File/document challenge area
│   │   ├── portal.html               # Login / profile surfaces
│   │   ├── contact.html              # SOC contact and SSRF-related test page
│   │   ├── robots.txt                # Crawling instructions / god mode clue
│   │   ├── hidden-9821/              # Hidden directory challenge path
│   │   ├── dev-portal-v1/            # Unlinked legacy portal
│   │   ├── assets/                  # CSS, JS, and static client assets
│   │   └── api/                     # Vulnerable backend endpoints
│   ├── application/                  # PHP config, DB, JWT helpers
│   ├── challenge-services/           # Isolated internal microservice
│   │   └── ssrf/
│   ├── challenge-secrets/            # Hidden flag registry and protected files
│   └── database/                     # SQL schema and seed data
└── .gitignore
```

## Quick start

### Option 1: Local runner
From the repository root:

```bash
python start_local.py
```

This starts a lightweight local HTTP server and serves the target site at:

```text
http://localhost:8080/
```

### Option 2: Docker Compose deployment
From the repository root:

```bash
docker compose up -d --build
```

Then browse to the deployed target app at:

```text
http://<TARGET-IP>/
```

If you're running locally with Docker Desktop, this usually resolves to:

```text
http://localhost/
```

## Notes on usage
- The project is intentionally vulnerable and should be used for training, practice, or controlled CTF-style exploitation.
- Flag values follow the `Flagfound{...}` format.
- The app is designed to be explored in-browser without any special challenge route or hidden launcher.
- No network privilege or external service is required for core local use.

## Intended workflow
1. Start the target environment.
2. Browse the E CORP website like a user.
3. Explore public pages, hidden directories, and API endpoints.
4. Trace the vulnerability chain from frontend behavior to backend logic.
5. Recover the underlying flag for each challenge.

## Files worth knowing
- `start_local.py` — local runner with embedded challenge logic and flag registry
- `target-site/public/api/` — challenge API endpoints and vulnerable backend behavior
- `target-site/database/schema.sql` — database schema used by the target app
- `target-site/challenge-secrets/flags.php` — central flag store for the challenge set
- `target-site/challenge-services/ssrf/server.py` — isolated SSRF target used by a challenge

## License and use
This repository is intended for educational and laboratory use. Use it in a controlled environment only.
