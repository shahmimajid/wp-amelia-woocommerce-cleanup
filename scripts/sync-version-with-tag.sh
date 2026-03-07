#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: $0 <version-or-tag>"
  echo "Example: $0 v1.2.0"
  exit 1
fi

RAW_VERSION="$1"
VERSION="${RAW_VERSION#v}"

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_FILE="$ROOT_DIR/plugin/wp-amelia-woocommerce-cleanup/wp-amelia-woocommerce-cleanup.php"
CHANGELOG_FILE="$ROOT_DIR/CHANGELOG.md"

if [[ ! -f "$PLUGIN_FILE" ]]; then
  echo "Plugin file not found: $PLUGIN_FILE"
  exit 1
fi

perl -0pi -e "s/Version:\s*[0-9]+\.[0-9]+\.[0-9]+/Version: ${VERSION}/" "$PLUGIN_FILE"
perl -0pi -e "s/define\('AWC_PLUGIN_VERSION',\s*'[^']+'\);/define('AWC_PLUGIN_VERSION', '${VERSION}');/" "$PLUGIN_FILE"

if [[ -f "$CHANGELOG_FILE" ]] && ! rg -q "^## ${VERSION}$" "$CHANGELOG_FILE"; then
  python - "$CHANGELOG_FILE" "$VERSION" <<'PY'
from pathlib import Path
import sys

path = Path(sys.argv[1])
version = sys.argv[2]
text = path.read_text()
section = f"## {version}\n\n- TBD\n\n"
if text.startswith("# Changelog\n\n"):
    text = text.replace("# Changelog\n\n", "# Changelog\n\n" + section, 1)
else:
    text = section + text
path.write_text(text)
PY
fi

echo "Synced plugin version to ${VERSION}"
