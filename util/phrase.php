<?php
  /**
   * 1. 利用本工具將 同義詞文字檔轉成 json
   * 2. 建好 phrase 的 index: 
   *    config/index/phraseIndex.json
   * 3. 將轉換後的 json 餵進搜尋引擎:
   *    php gosearch.php Index importFile gohappy phrase util/phrase1.json
   * 4. 修改對應的 json/ui/output schema
   *    config/search/phrase*
   */

  /**
   * processWords
   *
   * @param string $words words
   *
   * @return arrat
   */
  function processWords($words)
    {
        $list = explode(',', mb_strtolower(trim($words), 'utf-8'));
        $list = array_unique($list);
        $arr  = [];
        foreach ($list as $value) {
            $value = trim($value);
            if ('' == $value) {
                continue;
            }

            $arr[] = $value;
        }

        if (count($arr) <= 1) {
            // 同義詞至少要兩個以上才能做關聯
            return false;
        }

        natsort($arr);
        // array_unique 有跳號的問題 ex: 0,1,3,4  ElasticSearch 無法處理
        // 因此需透過 array_values 修正
        return array_values($arr);
    }

    $fp = fopen("phrase.txt", "r");
    while($buf = fgets($fp)) {
        $words = processWords($buf);
        if (!$words) {
            continue;
        }
        $data = ['docid' => $words[0], 'words' => $words];
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
    }
