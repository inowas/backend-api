#bin/bash

docker compose exec db psql -U api-platform -d api -P pager=off -c "select calculation::json->'latest' as latest from modflow_model where is_archived=False and calculation::text!='[]'" | grep '".*"'