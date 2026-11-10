# PROJECT: BLACKOUT — INSTALLATION & SYSTEM PREREQUISITES

## System Requirements
- **Operating System:** Ubuntu Server 22.04 LTS or 24.04 LTS (x86_64 or ARM64)
- **CPU:** 2 Cores minimum (4 Cores recommended for 50+ participants)
- **RAM:** 4 GB minimum (8 GB recommended)
- **Storage:** 20 GB free disk space
- **Network:** Isolated CTF VLAN, college laboratory network, or local host loopback

## Installing Docker & Docker Compose on Ubuntu

### 1. Update Package Index & Install Dependencies
```bash
sudo apt-get update
sudo apt-get install -y ca-certificates curl gnupg lsb-release
```

### 2. Add Docker's Official GPG Key
```bash
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
```

### 3. Add Docker Apt Repository
```bash
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
```

### 4. Install Docker Engine and Compose Plugin
```bash
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

### 5. Verify Docker Installation
```bash
sudo docker --version
sudo docker compose version
```

### 6. Grant User Privileges (Optional)
```bash
sudo usermod -aG docker $USER
newgrp docker
```
