<?php
#This file is not for public access.
if (defined("INDEXING", "SOLR/")) {
  die("Not for public.");
}
#Assume that the collection/index name is "slims".
?>

curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"biblio_id", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"title", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"gmd_name", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"sor", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"edition", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"isbn_issn", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"publisher_name", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"publish_year", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"collation", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"series_title", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"call_number", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"language_name", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"source", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"place", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"classification", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"notes", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"image", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"promoted", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"opac_hide", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"labels", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"frequency", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"spec_detail_info", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"content_type", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"media_type", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"carrier_type", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"uid", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"input_date", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"last_update", "type":"text_general", "multiValued":false, "stored":true}}' http://localhost:8983/solr/slims/schema

curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"authors.author_name", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"authors.authority_type", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"authors.authority_level", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema

curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"subjects.topic", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"subjects.topic_type", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"subjects.topic_level", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema

curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.item_id", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.item_code", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.call_number", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.coll_type_name", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.shelf_location", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.location_name", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.inventory_code", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.item_status", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.order_no", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.order_date", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.received_date", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.supplier_name", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.source", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.invoice", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.invoice_date", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.price", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.price_currency", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.input_date", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.last_update", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema
curl -X POST -H 'Content-type:application/json' --data-binary '{"add-field":{"name":"items.uid", "type":"text_general", "multiValued":true, "stored":true}}' http://localhost:8983/solr/slims/schema

curl -X POST -H 'Content-type:application/json' --data-binary '{"add-copy-field" : {"source":"*","dest":"_text_"}}' http://localhost:8983/solr/slims/schema

