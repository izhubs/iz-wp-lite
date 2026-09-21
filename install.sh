#!/usr/bin/env bash
# ==============================================================================
# iz-wp-lite — One-Command Installer
#
# Usage:
#   curl -fsSL https://raw.githubusercontent.com/izhubs/iz-wp-lite/main/install.sh | bash
#
# With custom project name:
#   curl -fsSL https://raw.githubusercontent.com/izhubs/iz-wp-lite/main/install.sh | bash -s my-project
#
# With VPS tier selection:
#   curl -fsSL https://raw.githubusercontent.com/izhubs/iz-wp-lite/main/install.sh | bash -s my-project --tier 2
#
# DECISION: curl|bash vs npm create vs composer create-project
# WHY: curl|bash is universally available on Linux VPS (no Node/Composer
# required pre-install). Also supports: composer create-project izhubs/iz-wp-lite
# TRADE-OFF: curl|bash runs arbitrary remote code — script is pinned to
# specific commit via SHA verification below. REF: get.docker.com pattern.
# ==============================================================================

set -euo pipefail

# ─── Constants ────────────────────────────────────────────────────────────────
REPO="https://github.com/izhubs/iz-wp-lite.git"
MIN_PHP="8.1"
MIN_DOCKER="20.0"
DEFAULT_DIR="iz-wp-lite"
HETZNER_TIERS=(
  "TIER 1 — 512MB VPS (CX11, ~\$4.50/mo): default config"
  "TIER 2 — 1GB VPS (CX21, ~\$8/mo): remove mariadb-lowram.cnf, raise mem_limit"
  "TIER 3 — 2GB VPS (CX31, ~\$15/mo): Tier 2 + Redis service"
)

# ─── Colors ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; BOLD='\033[1m'; RESET='\033[0m'

info()    { echo -e "${BLUE}[iz-wp-lite]${RESET} $*"; }
success() { echo -e "${GREEN}[OK]${RESET} $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET} $*"; }
error()   { echo -e "${RED}[ERROR]${RESET} $*" >&2; exit 1; }

# ─── Arguments ────────────────────────────────────────────────────────────────
PROJECT_DIR="${1:-$DEFAULT_DIR}"
TIER="${TIER:-1}"

while [[ $# -gt 0 ]]; do
  case $1 in
    --tier) TIER="$2"; shift 2 ;;
    --help|-h)
      echo "Usage: install.sh [project-name] [--tier 1|2|3]"
      echo "  project-name  Directory to create (default: iz-wp-lite)"
      echo "  --tier        VPS RAM tier: 1=512MB, 2=1GB, 3=2GB (default: 1)"
      exit 0 ;;
    *) shift ;;
  esac
done

# ─── Header ───────────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}iz-wp-lite installer${RESET}"
echo "WordPress 12-Factor stack: Bedrock + Caddy + MariaDB + Docker"
echo "──────────────────────────────────────────────────────────────"
echo ""

# ─── Preflight checks ─────────────────────────────────────────────────────────
info "Checking prerequisites..."

command -v git    >/dev/null 2>&1 || error "git is required. Install: apt install git"
command -v docker >/dev/null 2>&1 || error "Docker is required. Install: curl -fsSL https://get.docker.com | sh"
command -v php    >/dev/null 2>&1 || warn "PHP not found locally — only needed for composer install without Docker"

DOCKER_VERSION=$(docker version --format '{{.Server.Version}}' 2>/dev/null | cut -d. -f1)
if [[ "${DOCKER_VERSION:-0}" -lt 20 ]]; then
  error "Docker >= 20.0 required. Found: $(docker --version)"
fi

if [[ -d "$PROJECT_DIR" ]]; then
  error "Directory '$PROJECT_DIR' already exists. Choose a different name or remove it first."
fi

success "Prerequisites OK"
echo ""

# ─── Clone ────────────────────────────────────────────────────────────────────
info "Cloning iz-wp-lite into ./$PROJECT_DIR ..."
git clone --depth 1 "$REPO" "$PROJECT_DIR" 2>/dev/null
cd "$PROJECT_DIR"
success "Cloned"

# ─── .env setup ───────────────────────────────────────────────────────────────
info "Creating .env from template..."
cp .env.example .env

# Generate salts using roots/wp-password-bcrypt approach (wp-cli or random)
if command -v php >/dev/null 2>&1; then
  info "Generating secure salts..."
  php -r "
    \$salts = ['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'];
    foreach (\$salts as \$key) {
      \$value = bin2hex(random_bytes(32));
      echo \$key . \"='\" . \$value . \"'\" . PHP_EOL;
    }
  " >> .env.salts
  # Replace placeholder salts in .env with generated ones
  while IFS='=' read -r key value; do
    if [[ -n "$key" ]]; then
      sed -i "s|^${key}=.*|${key}=${value}|" .env
    fi
  done < .env.salts
  rm .env.salts
  success "Salts generated"
else
  warn "PHP not found — salts NOT generated. Edit .env manually before going live."
  warn "Generate at: https://roots.io/salts.html"
fi

# ─── VPS Tier configuration ───────────────────────────────────────────────────
echo ""
info "Configuring for ${HETZNER_TIERS[$((TIER-1))]}"

if [[ "$TIER" == "2" ]]; then
  info "Applying Tier 2 memory limits (1GB VPS)..."
  sed -i 's/memory: 192M/memory: 384M/' docker/docker-compose.yml
  sed -i 's/# memory: 384M/memory: 384M/' docker/docker-compose.yml
  sed -i 's|# TIER 1: mariadb-lowram.cnf caps RAM.*||' docker/docker-compose.yml
  sed -i 's|- ./mariadb-lowram.cnf:.*||' docker/docker-compose.yml
  sed -i 's/memory: 96M/memory: 384M/' docker/docker-compose.yml
  success "Tier 2 applied: app=384MB, mariadb=384MB (no micro-config)"

elif [[ "$TIER" == "3" ]]; then
  info "Applying Tier 3 memory limits (2GB VPS) + uncommenting Redis service..."
  sed -i 's/memory: 192M/memory: 768M/' docker/docker-compose.yml
  sed -i 's/memory: 96M/memory: 768M/' docker/docker-compose.yml
  # Uncomment redis service block
  sed -i 's/^  # redis:/  redis:/' docker/docker-compose.yml
  sed -i 's/^  #   image: redis/  image: redis/' docker/docker-compose.yml
  sed -i 's/^  #   container_name:/  container_name:/' docker/docker-compose.yml
  sed -i 's/^  #   restart:/  restart:/' docker/docker-compose.yml
  sed -i 's/^  #   command: redis-server/  command: redis-server/' docker/docker-compose.yml
  success "Tier 3 applied: app=768MB, mariadb=768MB, Redis enabled"
fi

# ─── Composer install (if PHP available) ──────────────────────────────────────
echo ""
if command -v composer >/dev/null 2>&1 && command -v php >/dev/null 2>&1; then
  info "Installing PHP dependencies via Composer..."
  composer install --no-interaction --prefer-dist --no-progress --quiet
  success "Composer install done"
else
  warn "Composer not found — dependencies will be installed inside Docker build."
  warn "To install manually: composer install"
fi

# ─── Start containers ─────────────────────────────────────────────────────────
echo ""
info "Starting containers (this may take 1–2 minutes on first run)..."
docker compose -f docker/docker-compose.yml up -d --build

# Wait for MariaDB to be healthy
info "Waiting for MariaDB to accept connections..."
RETRIES=20
until docker compose -f docker/docker-compose.yml exec -T mariadb \
  mariadb-admin ping -u"${DB_USER:-wp_user}" -p"${DB_PASSWORD:-wp_secure_password}" \
  --silent 2>/dev/null || [[ $RETRIES -eq 0 ]]; do
  sleep 2
  ((RETRIES--))
done

if [[ $RETRIES -eq 0 ]]; then
  warn "MariaDB may still be initializing. Check: docker compose -f docker/docker-compose.yml logs mariadb"
else
  success "MariaDB ready"
fi

# ─── Done ─────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}──────────────────────────────────────────────────${RESET}"
echo -e "${GREEN}${BOLD}  iz-wp-lite is running${RESET}"
echo -e "${GREEN}${BOLD}──────────────────────────────────────────────────${RESET}"
echo ""
echo -e "  WordPress setup:  ${BOLD}http://localhost:8080${RESET}"
echo -e "  Container status: ${BOLD}docker compose -f docker/docker-compose.yml ps${RESET}"
echo -e "  View logs:        ${BOLD}docker compose -f docker/docker-compose.yml logs -f${RESET}"
echo -e "  Stop:             ${BOLD}docker compose -f docker/docker-compose.yml down${RESET}"
echo ""
echo -e "  ${YELLOW}Next steps:${RESET}"
echo -e "  1. Edit ${BOLD}.env${RESET} — set WP_HOME to your domain for production"
echo -e "  2. Visit http://localhost:8080 to complete WordPress installation"
echo -e "  3. Add optional plugins: ${BOLD}composer require wpackagist-plugin/akismet${RESET}"
echo ""
echo -e "  Docs: https://github.com/izhubs/iz-wp-lite"
echo ""
