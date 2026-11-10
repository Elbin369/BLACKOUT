# PROJECT: BLACKOUT — TARGET SITE

Web Exploitation CTF 2026 Target Environment  
"Every system has a weakness. Find yours."

## Overview
This is the participant-facing target corporate website for **E CORP**.
It contains 20 deliberately introduced web vulnerabilities ranging across Easy, Medium, Hard, and Advanced difficulties.

## Features
- Realistic E Corp corporate web interface
- Simulated interactive terminal with pre-configured safe commands
- 20 unique flags matching `Flagfound{...}`
- Hidden challenge-secrets directory protected outside public web root
- Isolated internal microservice for SSRF (A01)

## Running the Target
### Using Docker Compose:
```bash
docker compose up -d --build
```
Visiting `http://TARGET-IP/` opens `target-site/public/index.html`.
