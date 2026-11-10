# PROJECT: BLACKOUT

### WEB EXPLOITATION CTF 2026
*"Every system has a weakness. Find yours."*

---

## Executive Overview
**PROJECT: BLACKOUT** is a professional, production-grade web exploitation Capture The Flag (CTF) target platform.
Participants receive a single target corporate website (`E CORP`) and must conduct a realistic penetration assessment across realistic corporate pages, APIs, endpoints, and client-side assets to discover and exploit 20 deliberately introduced vulnerabilities.

- **Total Challenges:** Exactly 20
- **Difficulty Distribution:** 3 Easy, 7 Medium, 5 Hard, 5 Advanced
- **Total Points Pool:** 3,350 Points
- **Flag Format:** `Flagfound{...}`
- **Entry Point:** Root URL `http://TARGET-IP/` automatically loads `index.html`. No special CTF routes or spoilers.

---

## Directory Architecture
```
project-blackout/
├── target-site/                        # Vulnerable E Corp corporate website
│   ├── public/                         # Public web root (opens index.html)
│   │   ├── index.html                  # Main entry point & terminal simulation (E01)
│   │   ├── about.html                  # Corporate overview & leadership bios
│   │   ├── services.html               # Enterprise services tier (M02)
│   │   ├── news.html                   # Intelligence news search (H01)
│   │   ├── documents.html              # Corporate documents & uploads (H02, H05)
│   │   ├── portal.html                 # SSO login & employee profiles (M01, M07, H03)
│   │   ├── contact.html                # SOC contact & webhook tester (A01)
│   │   ├── robots.txt                  # Crawler exclusion file (E02)
│   │   ├── hidden-9821/                # Restricted report directory (E02)
│   │   ├── dev-portal-v1/              # Unlinked legacy portal (M03)
│   │   ├── assets/                     # CSS, JS, and image assets
│   │   └── api/                        # Backend REST & challenge endpoints
│   ├── application/                    # DB handlers, JWT, helpers, config
│   ├── challenge-services/             # Isolated SSRF microservice (A01)
│   ├── challenge-secrets/              # Central flag registry (OUTSIDE web root)
│   ├── database/                       # MariaDB / SQLite schema and synthetic seed
│   ├── Dockerfile                      # Target container definition
│   └── docker-compose.yml              # Target environment orchestration
├── documentation/                      # Complete operational manuals
│   ├── installation.md                 # Ubuntu & Docker setup guide
│   ├── deployment.md                   # Master deployment instructions
│   ├── participant-guide.md            # Participant field manual
│   ├── organizer-guide.md              # Organizer event administration manual
│   ├── testing.md                      # Comprehensive testing report
│   ├── troubleshooting.md              # Troubleshooting guide & FAQs
│   └── private-walkthroughs/           # 20 Individual private challenge walkthroughs
├── tests/                              # Verification test suites
│   ├── test_in_memory.py               # Zero-network full challenge verification test
│   └── test_all_challenges.py          # HTTP end-to-end exploit runner
├── start_local.py                      # Local instant runner (Python)
└── docker-compose.yml                  # Root orchestration for deployment
```

---

## Quick Start (Production Deployment)

### 1. Launch via Docker Compose
From the repository root:
```bash
docker compose up -d --build
```

### 2. Verify Access
- **Target Corporate Website:** `http://<TARGET-IP>/` (Directly loads `index.html`)

### 3. Local Test Execution
```bash
python start_local.py
```
Or run the zero-network automated verification test suite:
```bash
python tests/test_in_memory.py
```
Outputs the verification matrix confirming 20 / 20 passing challenge exploits and 3,350 total points.

---

## Documentation Links
- [Installation Guide](file:///documentation/installation.md)
- [Deployment Guide](file:///documentation/deployment.md)
- [Participant Field Manual](file:///documentation/participant-guide.md)
- [Organizer Marshal Guide](file:///documentation/organizer-guide.md)
- [Testing Report (100% Pass)](file:///documentation/testing.md)
- [Troubleshooting & FAQs](file:///documentation/troubleshooting.md)
- [Private Challenge Walkthroughs (E01 to A05)](file:///documentation/private-walkthroughs/)
