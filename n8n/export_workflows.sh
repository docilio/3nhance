# This allows to export all the workflows into their right folders
# It will then rename the files with the project titles

mkdir -p ~/n8n_shared/workflow_exports/$(date +%Y-%m-%d)

docker exec n8n \
  n8n export:workflow --all --separate \
  --output="/home/node/n8n_shared/workflow_exports/$(date +%Y-%m-%d)/"

EXPORT_DIR=~/n8n_shared/workflow_exports/$(date +%Y-%m-%d)

cd "$EXPORT_DIR" || exit 1

for file in *.json; do
  # Extract workflow name from JSON
  name=$(jq -r '.name' "$file")

  # Skip if name couldn't be extracted
  if [ -z "$name" ] || [ "$name" == "null" ]; then
    echo "Skipping $file: no name found"
    continue
  fi

  # Clean the name for use as filename (remove slashes, etc.)
  safe_name=$(echo "$name" | tr -cd '[:alnum:]_-')

  # Handle potential name conflicts
  new_file="${safe_name}.json"
  i=1
  while [ -e "$new_file" ]; do
    new_file="${safe_name}_$i.json"
    ((i++))
  done

  # Rename the file
  mv "$file" "$new_file"
  echo "Renamed $file -> $new_file"
done
