<?php

# dump data from elasticsearch
# curl http://10.100.80.119:9200/shoppingplus_product/_search -d '{ "from": 0, "size": 8000 }' > /tmp/data

$data = file_get_contents("/tmp/data");
$x = json_decode($data, 1);
foreach ($x['hits']['hits'] as $hits) {
    echo json_encode($hits['_source'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
