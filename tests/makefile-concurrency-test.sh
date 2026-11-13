#!/bin/sh
set -eu

root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
directory=$(mktemp -d)
trap 'rm -rf "$directory"' EXIT

cp "$root/Makefile" "$root/composer.json" "$directory/"
cp -R "$root/scripts" "$directory/scripts"
mkdir "$directory/bin"
cat > "$directory/bin/docker" <<'DOCKER'
#!/bin/sh
if ! mkdir "$SENTINEL" 2>/dev/null; then
    touch "$RACE"
    exit 42
fi
sleep 1
rmdir "$SENTINEL"
exit 42
DOCKER
chmod +x "$directory/bin/docker"

export PATH="$directory/bin:$PATH" SENTINEL="$directory/active" RACE="$directory/race"
make -s -C "$directory" install PHP_VERSION=8.1 COMPOSER_CACHE_DIR="$directory/cache" >/dev/null 2>&1 &
first=$!
make -s -C "$directory" install PHP_VERSION=8.1 COMPOSER_CACHE_DIR="$directory/cache" >/dev/null 2>&1 &
second=$!
first_status=0
second_status=0
wait "$first" || first_status=$?
wait "$second" || second_status=$?
if [ "$first_status" -eq 0 ] || [ "$second_status" -eq 0 ]; then
    echo 'install unexpectedly succeeded' >&2
    exit 1
fi
test ! -e "$RACE"
test -z "$(find "$directory" -maxdepth 1 -name '.composer-run.*' -print)"
