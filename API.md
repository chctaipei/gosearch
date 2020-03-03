API

搜尋
1. 熱門關鍵字:
   [GET] /api/search/{project}/_hotwords

2. 建議詞(autocomplete)
   [POST] /api/search/{project}/_suggest
   {
   "keyword": "a"
   }

3. 模糊關鍵字
   [POST] /api/search/{project}/_fuzzy
   {
   "keyword": "平果"
   }

4. 套版搜尋
   [POST] /api/search/{project}/{type}/_search/{scriptId}
   {
    "from": 0,
    "size": 10,
    "query": "好吃",
    "filter": {
        "now_price": {}
    },
    "aggs": false,
    "sort": {
        "by": "_score",
        "order": "DESC"
    }
   }

5. 非套版搜尋
   [POST] /api/search/{project}/{type}/_search
   {
    "from": 0,
    "size": 10
   }

6. 紀錄關鍵字
   [POST] /api/search/{project}/_log
   {
    "keyword": "aaaa",
    "matches": 10
   }

文件
1. 取得文件
   [GET] /api/{project}/{type}/{docId}

2. 修改文件
   [PUT] /api/{project}/{type}/{docId}
   {
     "keyword": "aaa"
   }

3. 新增文件
   [POST] /api/{project}/{type}/{docId}

熱門關鍵字
1. 修改分數
   [PUT] /api/hotwords/{project}
   {
     "query": "aaa",
     "count": 10
   }

2. 刪除熱門關鍵字/新增黑名單
   [PUT] /api/hotwords/{project}
   {
     "query": "aaa"
   }

排除關鍵字
1. 新增黑名單
   [POST] /api/badword/{project}
   {
     "query": "aaa"
   }

2. 刪除黑名單
   [DELETE] /api/badword/{project}
   {
     "query": "aaa"
   }

服務
1. 啟用或停止 cron server
   [PUT} /api/service/cron
   {
     "active": 1
   }

專案
1. 取得 project
   [GET] /api/project/{project}

2. 建立 project
   [POST] /api/project/{project}

3. 刪除 project
   [DELETE] /api/project/{project}
   {
     "password": 'abcd1234'
   }

4. 更新 search script
   [PUT] /api/project/{project}/script/{scriptId}
   { .... }

5. 更新 search schema (非必須)
   [PUT] /api/project/{project}/schema/{scriptId}
   { .... }

5. 更新 project 設定 (config = index,source,import,backup,cronjob)
   [PUT] /api/project/{project}/config/{config}

Cronjob
6. 更新 cronjob task (與上面的 /config/cronjob 差在 task 參數一個要放在 body 裡面)
   [PUT] /api/project/{project}/cronjob/{task}

7. 執行 cronjob
   [POST] /api/project/{project}/cronjob/{task}/run
   {
     'jobid': 123
   }

8. 開關 conjob (帶 type 以區別相同的 task name)
   [PUT] /api/project/{project}/cronjob/{task}/active
   {
     'active': 1,
     'type': 'product'
   }

9. 刪除 index mapping
   [DELETE] /api/project/{project}/mapping/{type}

10. 切換 Index alias
   [POST] /api/project/{project}/alias
   {
     'type': 'product',
     'id': 2
   }
