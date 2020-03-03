# friday 搜尋引擎使用說明

## 專案建立
1.  一個專案會配一個 HotQuery 做 hotquery, 同音, autocomplete, 同義詞, 如果不夠則要另開新專案
2. 專案會建立 {project}_badword 及 {project}_hotquery 兩個 table
3. task: Admin initProject 不帶參數時會自動從 config/project/ 目錄取得並建立
3.1 config/project/{project}/config.json 說明:
  * backup: 對應 gosearch.php Admin setBackup PROJECT TYPE BACKUP     設定 index 的備分數
  * import: 對應 gosearch.php Admin setImport PROJECT TYPE SOURCENAME 設定 index 與資料來源的對應關係
  * source: 對應 gosearch.php Admin setSource PROJECT SOURCENAME JSON 新增資料來源
3.2 預設的 index 及 search script 需依照一定格式及檔名存放
  * config/project/{project}/index
     - gosearch.php Admin setIndex PROJECT TYPE JSON/FILE 新增或修改 type mapping 
     - gosearch.php Index create   PROJECT all            建立索引 ('all' 表示全部)
  * config/project/{project}/search
     - gosearch.php Index putTemplate                     設定索引樣板
3.3 注意: initProject 會複寫掉已存在的專案!!!!

## 索引管理
1. 建立步驟為
1.1 建立 script/settings
1.2 建立 index
2. 一旦 index 建立後，必須刪除 index 才能修改 script 或 settings
3. index script 內容存放在 mysql db
4. index 輪替, 只有 cron job 從 db 餵資料會自動切換, 如果是自己主動 feed 到 search engine，
   則必須自己處理 index 切換 (importData)
5. 當索引內容要採用被動從 database 餵入的方式時, 要先建立"設定資料源"

## 搜尋腳本
1. 搜尋腳本不是必要的流程，但使用腳本可以簡化搜尋傳遞內容，可線上修改例如: 權重
2. 腳本建立後，會出現在左側 sidebar
3. 搜尋設定包含: (只會在管理工具呈現)
3.1 JSON Schema - 資料驗證及 form  (json)
3.2 UI Schema - form 的順序及 style  (json)
3.3 Search Script - 搜尋腳本 (mustache)
3.4 Output Script - 輸出套版 (handlebars -- 與 mustache 相通)
3.5 Index Type - 搜尋指定 index 
4. fuzzy 模糊搜尋
4.1 執行 syncDic 時才會建立及更新
4.2 相關的腳本可參考 config/search/fuzzySearch*
5. autocomplete 建議關鍵字
5.1 


## 熱門關鍵字
1. 記錄方式
1.1 由 client 端透過 [POST] /api/search/{project}/_log
    {
        "keyword": %keyword%,
        "matches": %matches
    }
1.2 或 [TODO] 搜尋腳本指定... (待完成)
1.3 或 [TODO] 無腳本搜尋... (待完成)
2. 排除關鍵字
2.1 可以用 a.一般關鍵字 或 b. regex 的方式排除
2.2 使用 regex 排除前請先做測試
3. 熱門關鍵字的資料庫內容也會用在 autocomplete 及 fuzzy 模糊搜尋
4. 熱門關鍵字依分數排序，分數可透過 resetCounter 重設


## 設定資料源
1. 資料源為 PDO 所支援的資料庫， mysql/oracle/postgreSQL
2. 透過 sql 語法與外掛 filter 產出搜尋所需要的內容
3. filter 目錄位於 src/Plugin/Filter/
3.1 filter 範例: 將每筆取出的資料做轉換
    public function filter($data)
    {
        // 將 categories 字串轉為 array
        if ($data['categories'] ?? false) {
            $data['categories'] = json_decode($data['categories'], 1);
        }

        return $data;
    }
4. 資料源建立好之後，就可以在索引管理介面做綁定，完成後才能使用 importData


## 定期排程
1. resetCounter - 重設熱門關鍵字的計數器
1.1 重設 hotquery 的 score，以避免太過老舊的熱門關鍵字一直出現
2.2 建議一周一次，週期長短可依專案需要做調整
2. syncDic - 同步同音詞庫 (關鍵字存在超過 days=1 天, 取前 count=6000 筆)
2.1 days 表示建立時間要大於多少天, 用來篩選非熱門詞
2.2 count 表示取多少筆數做為同音詞庫
2.3 syncDic 須完成一次使用，才會建立 fuzzy index，而每次使用都會重建 fuzzy index
2.2 建議每天或每周一次
3. syncMatches - 同步熱門關鍵字的顯示數量, 於資料匯入完成後執行
3.1 tag 代表關鍵字的標籤名稱 (預設為 query)
3.2 body 配合腳本送出的 JSON 內容，一般不用設定
3.3 index 執行同步時對應的索引檔
3.4 script 執行同步時對應的搜尋腳本
3.5 使用 syncMatches 需先建立搜尋腳本 (腳本可以是獨立的不須共用)
3.6 建議於資料匯入後執行，或更短周期
4. importData - 匯入資料到搜尋引擎
4.1 importData 可以有多組 (每個 index type 都可以設定一組)
4.2 通常需要在 import 結束後執行 syncMatches 同步數量
4.3 importData 會自動選擇並切換輪替的 index

## 其他
1. 輪替索引的選擇順序
   a. 先檢查是否有空的
   b. 如果有空的, 則選擇最小的號碼
   c. 如果沒有空的, 則選擇最久的紀錄作刪除

2. 同義詞庫
   * 各專案自行建立

3. 熱門關鍵字紀錄
   * 獨立運作，一個專案只能有一個熱搜 log
   * gosearch.php Search log PROJECT KEYWORD MATCHES 
   => 熱門關鍵字排行榜

3. 點擊紀錄
   * 獨立運作，一個專案只能有一個點擊 log
   * gosearch.php Search click PROJECT KEYWORD MATCHES 
   => 行為優化排序 (參考 TODO)

## 參考
1. index alias 更改對應表的做法
POST /_aliases
{
  "actions" : [
    { "remove" : { "index" : "gohappy_product_1", "alias" : "gohappy_product" } },
    { "add" : { "index" : "gohappy_product_2", "alias" : "gohappy_product" } }
  ]
}

1.1 Admin setBackup PROJECT TYPE n  (n: backups, 0: normal)
config: 
  ['data']['backups'] = [
       'product' => ['counts' => 3, '1' => $create_time, '2' => $create_time, '3' => $create_time]
       'phrase'  => ['counts' => 0]
  ]

1.2 Project switch PROJECT TYPE (切換 backup)

2. index,search template
index 存 DB, search script, template, json schema 存 ElasticSearch，建議 search script 要自己做備份

3. sync template 註記在 DB

4. 同音是利用 hotquery 建立的 index, 作法是將關鍵字轉換成同音字後與 phonetic 做 fuzzy 比對
因為是利用 hotquery, 所以初期需要累積一些 query log 才能提高精準度

## TODO
帳號:
  1. 帳號依專案做控管

搜尋加權: (行為優化)
  1. 商品點擊要 feedback 回搜尋引擎
  2. 留 keyword 欄位讓搜尋引擎自動記錄到文件裡

## BUG
1.  Elastic Search 的 mustache script parsing 有一個 bug

  正常:
  {{#xxx}}12345{{/xxx}}

  不正常: 在 tag 前後如果有多的 { 或 } 會導致 parsing 錯誤
  {{{#xxx}}12345{{/xxx}}}

  要改成:
  { {{#xxx}}12345{{/xxx}} }

  參考:
  https://mustache.github.io/mustache.5.html
  triple mustache: {{{name}}} 有其他意義, 因此須避免這個問題

2. cron daemon 如果執行發生 Exception 將造成 task 中斷, 狀態變成執行中而無法再被執行
