#!/bin/sh
set -eu

root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
directory=$(mktemp -d)
trap 'rm -rf "$directory"' EXIT

mkdir "$directory/bin"
printf '#!/bin/sh\nexit 0\n' > "$directory/bin/php"
chmod +x "$directory/bin/php"

if (cd "$directory" && PATH="$directory/bin:$PATH" PREPARED=1 MODE=audit PHP_VERSION=8.1 \
    sh "$root/scripts/container-qa.sh" >audit.log 2>&1); then
    echo 'audit unexpectedly succeeded without installed dependencies' >&2
    exit 1
fi
grep -q 'No installed dependencies to audit' "$directory/audit.log"
