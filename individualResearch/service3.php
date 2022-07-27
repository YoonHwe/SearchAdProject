<style>
    body{
        margin: 0;
    }
    .menu a:hover{
        cursor: pointer;
        color: white;
        background-color: rgb(2, 207, 92);
    }
</style>

<div style="display: flex; justify-content: space-between; border-bottom: 2px solid gray; padding: 1% 6%; background-color:white;" >
    <h3><a href="service1.php" style="text-decoration: none; color: black;">개별연구</a></h3>
    <div class="menu" style="display:flex; justify-content:center; align-items: center;">
        <a href="service1.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">키워드 수치 분석</a>
        <a href="service2.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">키워드 추이 분석</a>
        <a href="service3.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">연관 키워드 비교</a>
    </div>
</div>

<?php
    error_reporting(0);
    ini_set("default_socket_timeout", 30);
    require_once 'restapi.php';

    $config = parse_ini_file("sample.ini");

    function debug($obj, $detail = false){}
    $DEBUG = false;

    $api = new RestApi($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], $config['CUSTOMER_ID']);

    $reportType = "AD_DETAIL";
    $statDt = date('Ymd',strtotime("-1 days"));;
    $stat_req = array(
        "reportTp" => $reportType,
        "statDt" => $statDt
    );

    $response = $api->POST("/stat-reports", $stat_req);
    debug($response, $DEBUG);
    $reportjobid = $response["reportJobId"];
    $status = $response["status"];
    while ($status == 'REGIST' || $status == 'RUNNING' || $status == 'WAITING') {
        sleep(5);
        $response = $api->GET("/stat-reports/" . $reportjobid);
        $status = $response["status"];
    }
    if ($status == 'BUILT') {
        $api->DOWNLOAD($response["downloadUrl"], $reportType . "-" . $statDt . ".tsv");
    }
?>

<div style="background-color: #F7F9FA; border-bottom: 1px solid gray;padding-top: 2%;" >
    <h1 style="margin-left: 10%;">연관 키워드 비교</h1>
    <div>
        <p style="padding-left: 12%;">검색 키워드를 입력하시면,</p> 
        <p style="margin-top: -14px; padding-left: 12%;">연관 키워드와의 비교를 통해 순위를 파악하고</p>
        <p style="margin-top: -14px; padding-left: 12%;">상위 연관 키워드 20개의 지표를 보여줍니다.</p>
    <div>
    <form method="POST" id="api-call-form" style="padding-left: 12%; font-size: 32px;">
        검색 키워드: <input id="api-call-keyword" type="text" name="hintkeyword" style="border: 2px solid rgb(2, 207, 92); border-radius: 10px; font-size: 32px;"/>
    </form>
    <div id="localStorage_keyword" style="padding-left: 12%; padding-bottom: 4%; font-size: 16px; border-bottom: 1px solid gray;">
        <span>최근 검색: </span>
    </div>
    <?php
        error_reporting(0);
        function get_key() {
         echo "<script language='Javascript'>
         const localStorageKeyword = document.querySelector('#localStorage_keyword');
         for(let i = 0; i < localStorage.length-1; i++){
             if(localStorage.key(i).includes('recent_keyword')){
                 const spanForBid = document.createElement('span');
             
                 tmpLocal = localStorage.getItem(localStorage.key(i));
                 spanForBid.innerText = tmpLocal;
                 spanForBid.style.border = '1px solid black';
                 spanForBid.style.padding = '2px 4px';
                 spanForBid.style.margin = '0px 2px';
                 spanForBid.style.borderRadius = '10px';
                 localStorageKeyword.appendChild(spanForBid);
             }
             
         }
         </script>";
        }
        get_key();
    ?>
</div>

<?php
    error_reporting(0);
    function set_key($key, $data){
        $key = "recent_keyword_input_".$key;
        echo "<script language='Javascript'>localStorage.setItem('$key', '$data');</script>";
    }
    set_key($hintKeywords, $hintKeywords);
    $hintKeywords = $_POST['hintkeyword']; 
    $param = array (
        'format' => 'json', 
        'hintKeywords' => $hintKeywords, 
        'includeHintKeywords' => 0, 
        'siteId' => '', 
        'biztpId' => '', 
        'month' => '', 
        'event' => '', 
        'showDetail' => 1, 
        'keyword' => '' 
    ); 
    $tmp_list = $api->GET('/keywordstool', $param); 
    debug($tmp_list, $DEBUG);

    $tmp_list_keyword = $tmp_list["keywordList"];
    $target_keyword = $tmp_list_keyword[0];
    array_splice($tmp_list_keyword, 0, 1);
?>

<div style="display: flex; flex-direction: column; align-items: center; padding: 2% 0%; background-color: white;">
    <?php
        error_reporting(0);
        echo "<div style='margin-left: -50%;'>";
        echo("총 ".count($tmp_list_keyword)."개의 연관 검색어와 비교합니다."); 
        echo "</div>";
    ?>
    <table id="tableShop" class="table table-bordered table-striped" style="border: 1px solid black; border-collapse: collapse; text-align:center; margin-top: 12px;">
        <thead style="border: 1px solid black;">
        <tr>
            <th style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>-</th>
            <th style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>PC 검색수</th>
            <th style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>Mobile 검색수</th>
            <th style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>총 검색수</th>
            <th style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>PC 클릭수</th>
            <th style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>Mobile 클릭수</th>
            <th style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>총 클릭수</th>
            <th style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>PC 클릭률</th>
            <th style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>Mobile 클릭률</th>
        </tr>
        </thead>
        <tbody>
            <?php 
                error_reporting(0);
                echo "<tr>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo("데이터");
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($target_keyword['monthlyPcQcCnt']);
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($target_keyword['monthlyMobileQcCnt']);
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($target_keyword['monthlyPcQcCnt']+$target_keyword['monthlyMobileQcCnt']);
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($target_keyword['monthlyAvePcClkCnt']);
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($target_keyword['monthlyAveMobileClkCnt']);
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($target_keyword['monthlyAvePcClkCnt'] + $target_keyword['monthlyAveMobileClkCnt']);
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($target_keyword['monthlyAvePcCtr']);
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($target_keyword['monthlyAveMobileCtr']);
                echo "</td>";
                echo "</tr>";
            ?>
            <?php
                error_reporting(0);
                $pcQcCntRank = 0;
                $mobileQcCntRank = 0;
                $qcCntRank = 0;
                $pcClkRank = 0;
                $mobileClkRank = 0;
                $clkRank = 0;
                $pcCtr = 0;
                $mobileCtr = 0;
                for($i = 0; $i < count($tmp_list_keyword); $i++){
                    if($target_keyword['monthlyPcQcCnt'] < $tmp_list_keyword[$i]['monthlyPcQcCnt']){
                        $pcQcCntRank++;
                    }
                    if($target_keyword['monthlyMobileQcCnt'] < $tmp_list_keyword[$i]['monthlyMobileQcCnt']){
                        $mobileQcCntRank++;
                    }
                    if($tmp_list_keyword[$i]['monthlyPcQcCnt'] == "< 10"){
                        $tmp_list_keyword[$i]['monthlyPcQcCnt'] = 10; //현재 API에서 표기되고 있는 < 문자 제거
                    }
                    if($tmp_list_keyword[$i]['monthlyMobileQcCnt'] == "< 10"){
                        $tmp_list_keyword[$i]['monthlyMobileQcCnt'] = 10; //현재 API에서 표기되고 있는 < 문자 제거
                    }
                    if(($target_keyword['monthlyPcQcCnt']+$target_keyword['monthlyMobileQcCnt']) < ($tmp_list_keyword[$i]['monthlyPcQcCnt']+$tmp_list_keyword[$i]['monthlyMobileQcCnt'])){
                        $qcCntRank++;
                    }
                    if($target_keyword['monthlyAvePcClkCnt'] < $tmp_list_keyword[$i]['monthlyAvePcClkCnt']){
                        $pcClkRank++;
                    }
                    if($target_keyword['monthlyAveMobileClkCnt'] < $tmp_list_keyword[$i]['monthlyAveMobileClkCnt']){
                        $mobileClkRank++;
                    }
                    if($target_keyword['monthlyAvePcClkCnt'] + $target_keyword['monthlyAveMobileClkCnt'] < $tmp_list_keyword[$i]['monthlyAvePcClkCnt'] + $tmp_list_keyword[$i]['monthlyAveMobileClkCnt']){
                        $clkRank++;
                    }
                    if($target_keyword['monthlyAvePcCtr'] < $tmp_list_keyword[$i]['monthlyAvePcCtr']){
                        $pcCtr++;
                    }
                    if($target_keyword['monthlyAveMobileCtr'] < $tmp_list_keyword[$i]['monthlyAveMobileCtr']){
                        $mobileCtr++;
                    }
                }
                echo "<tr>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo("비교 순위");
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($pcQcCntRank+1)."위";
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($mobileQcCntRank+1)."위";
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($qcCntRank+1)."위";
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($pcClkRank+1)."위";
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($mobileClkRank+1)."위";
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($clkRank+1)."위";
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($pcCtr+1)."위";
                echo "</td>";
                echo "<td style='border: 1px solid black; font-size: 20px; padding: 4px 8px'>";
                echo($mobileCtr+1)."위";
                echo "</td>";
                echo "</tr>";
            ?>
        </tbody>
    </table>
</div>

<div style="border-top: 1px solid gray; padding-top: 2%; display: flex; justify-content: center; padding: 2% 0%;">
    <table id="tableShop" class="table tableForStick table-bordered table-striped" style="border: 1px solid black; border-collapse: collapse;">
        <thead style="border: 1px solid black;">
        <tr>
            <th style="border: 1px solid black; padding: 2px 4px">순서</th>
            <th style="border: 1px solid black; padding: 2px 4px">키워드</th>
            <th style="border: 1px solid black; padding: 2px 4px">총 검색수</th>
            <th style="border: 1px solid black; padding: 2px 4px">총 클릭수</th>
            <th style="border: 1px solid black; padding: 2px 4px">Pc 클릭률</th>
            <th style="border: 1px solid black; padding: 2px 4px">Mobile 클릭률</th>
            <th style="border: 1px solid black; padding: 2px 4px">평균 입찰가</th>
        </tr>
        </thead>
        <tbody>
            <?php 
                error_reporting(0);
                $index = 0; //연관검색어 추출 위한 index(20까지)
                $qcCntArray[21] = -1; //검색수 배열 초기화
                $clkCntArray[21] = -1; //클릭수 배열 초기화
                $keywordNames = array();
                
                while($index <= 20){ //연관검색어 20개 + 등록 검색어 1개까지
                    if($tmp_list['keywordList'][$index]['monthlyPcQcCnt'] == "< 10"){
                        $tmp_list['keywordList'][$index]['monthlyPcQcCnt'] = 10; //현재 API에서 표기되고 있는 < 문자 제거
                    }
                    $keywordNames[$index] = $tmp_list['keywordList'][$index]['relKeyword'];
                    $qcCntArray[$index] = ($tmp_list['keywordList'][$index]['monthlyPcQcCnt'] + $tmp_list['keywordList'][$index]['monthlyMobileQcCnt']); //총 검색수 = 모바일 + PC 검색수
                    //검색수 최대 최소 저장
                    
                    $clkCntArray[$index] = ($tmp_list['keywordList'][$index]['monthlyAvePcClkCnt'] + $tmp_list['keywordList'][$index]['monthlyAveMobileClkCnt']); //총 클릭수 = 모바일 + PC 클릭수
                    //클릭수 최대 최소 저장

                    $index++; //다음 연관검색어로 이동
                }

                $param = array(
                    'format' => 'json',
                    'device' => 'PC',
                    'period' => 'MONTH',
                    'items' => $keywordNames
                );
                $median_bid_api = $api->POST('/estimate/median-bid/keyword', $param);
                debug($median_bid_api, $DEBUG);
                $median_bid_list = $median_bid_api['estimate'];

                $index = 0; //테이블에 점수 등록하기 위한 index

                while($index <= 20){  //테이블에 있는 검색어 수만큼
                    echo "<tr>";
                    if($index == 0){
                        echo "<td style='border: 1px solid black; color: rgb(2, 207, 92); padding: 2px 4px'>$index</td>"; //순서    
                        echo "<td style='border: 1px solid black; color: rgb(2, 207, 92); padding: 2px 4px'>";
                        print_r(($tmp_list['keywordList'][$index]['relKeyword'])); //연관검색어
                        echo "</td>";

                        echo "<td style='border: 1px solid black; color: rgb(2, 207, 92); padding: 2px 4px'>";
                        echo ($qcCntArray[$index]); //검색 수
                        echo "</td>";

                        echo "<td style='border: 1px solid black; color: rgb(2, 207, 92); padding: 2px 4px'>";
                        echo ($clkCntArray[$index]); //클릭 수
                        echo "</td>";

                        echo "<td style='border: 1px solid black; color: rgb(2, 207, 92); padding: 2px 4px'>";
                        print_r(($tmp_list['keywordList'][$index]['monthlyAvePcCtr']));
                        echo "</td>";

                        echo "<td style='border: 1px solid black; color: rgb(2, 207, 92); padding: 2px 4px'>";
                        print_r(($tmp_list['keywordList'][$index]['monthlyAveMobileCtr']));
                        echo "</td>";
                        
                        echo "<td style='border: 1px solid black; color: rgb(2, 207, 92); padding: 2px 4px'>";
                        print_r($median_bid_list[$index]['bid']); //연관검색어
                        echo "</td>";
                        
                        echo "</tr>";
                    }
                    else{
                        echo "<td style='border: 1px solid black; padding: 2px 4px'>$index</td>"; //순서
                        echo "<td style='border: 1px solid black; padding: 2px 4px'>";
                        print_r(($tmp_list['keywordList'][$index]['relKeyword'])); //연관검색어
                        echo "</td>";

                        echo "<td style='border: 1px solid black; padding: 2px 4px'>";
                        echo ($qcCntArray[$index]); //검색 수
                        echo "</td>";

                        echo "<td style='border: 1px solid black; padding: 2px 4px'>";
                        echo ($clkCntArray[$index]); //클릭 수
                        echo "</td>";

                        echo "<td style='border: 1px solid black; padding: 2px 4px'>";
                        print_r(($tmp_list['keywordList'][$index]['monthlyAvePcCtr'])); //연관검색어
                        echo "</td>";

                        echo "<td style='border: 1px solid black; padding: 2px 4px'>";
                        print_r(($tmp_list['keywordList'][$index]['monthlyAveMobileCtr'])); //연관검색어
                        echo "</td>";

                        echo "<td style='border: 1px solid black; padding: 2px 4px'>";
                        print_r($median_bid_list[$index]['bid']); //연관검색어
                        echo "</td>";

                        echo "</tr>";
                    }
                    $index++; //다음 검색어로 이동
                }
            ?>
        </tbody>
        <tfoot>
        <tr>
        <th style="border: 1px solid black; padding: 2px 4px">순서</th>
            <th style='border: 1px solid black; padding: 2px 4px'>키워드</th>
            <th style='border: 1px solid black; padding: 2px 4px'>총 검색수</th>
            <th style='border: 1px solid black; padding: 2px 4px'>총 클릭수</th>
            <th style="border: 1px solid black; padding: 2px 4px">Pc 클릭률</th>
            <th style="border: 1px solid black; padding: 2px 4px">Mobile 클릭률</th>
            <th style="border: 1px solid black; padding: 2px 4px">평균 노출 단가</th>
        </tr>
        </tfoot>
    </table>
    <script>
        const tableForStick = document.querySelector(".tableForStick");
        const tableBody = tableForStick.querySelector("tbody");
        let keywordName = (tableBody.childNodes[1].childNodes[1].innerText);//키워드 이름
        let keywordQc = (tableBody.childNodes[1].childNodes[4].innerText);//키워드 검색수 string
        keywordQc = parseFloat(keywordQc);
        let keywordClk = (tableBody.childNodes[1].childNodes[5].innerText);//키워드 클릭수 string
        keywordClk = parseFloat(keywordClk);
        let keywordMedExp = (tableBody.childNodes[1].childNodes[6].innerText);//키워드 클릭수 string
        keywordMedExp = parseInt(keywordMedExp);
        let keywordAvg = Math.round((keywordQc + keywordClk )/ 2 * 100) / 100;

        let relatedPcCtr = 0;
        let relatedMobCtr = 0;
        let relatedPcAvg = 0;
        let relatedMobAvg = 0;
        let relatedAvg = 0;
        let relatedMedExp = 0;
        let relatedMedExpAvg = 0;
        for(let i = 2; i < tableBody.childNodes.length - 1; i++){
            relatedPcCtr = parseFloat(tableBody.childNodes[i].childNodes[4].innerText);
            relatedMobCtr = parseFloat(tableBody.childNodes[i].childNodes[5].innerText);
            relatedMedExp = parseInt(tableBody.childNodes[i].childNodes[6].innerText);
            relatedAvg += (relatedPcCtr + relatedMobCtr) / 2;
            relatedMedExpAvg += relatedMedExp;
        }
        relatedMedExpAvg = parseInt(relatedMedExpAvg / 20);
        let relatedAvgAvg = Math.round((relatedAvg/20)*100)/100;
        console.log(relatedPcCtr, relatedMobCtr, relatedAvgAvg);
    </script>
    <div style="width: 50%; margin-left: 5%; position: relative;">
        <canvas id="test1"></canvas>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
        <script>
            var ctx = document.getElementById('test1').getContext('2d');
            var chart = new Chart(ctx, {
                // The type of chart we want to create
                type: 'bar',

                // The data for our dataset
                data: {
                    labels: [keywordName, '연관 키워드 평균'],
                    datasets: [{
                        label: '검색 키워드',
                        backgroundColor: [
                            'rgb(2, 207, 92)',
                            'rgba(255, 206, 86, 0.5)'],
                        borderColor: [
                            'rgb(2, 207, 92)',
                            'rgba(255, 206, 86, 0.5)'],
                        data: [keywordAvg, relatedAvgAvg]
                    }]
                },

                // Configuration options go here
                options: {
                    title: {
                        display: true,
                        text: '평균 클릭률 비교',
                        fontSize: 24,
                        fontColor: 'rgba(46, 49, 49, 1)'
                    },
                    legend: {
                        labels: {
                            fontColor: 'rgba(83, 51, 237, 1)',
                            fontSize: 15
                        }
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                fontColor: 'rgba(27, 163, 156, 1)',
                                fontSize: '15'
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontColor: 'rgba(246, 36, 89, 1)',
                                fontSize: '15'
                            }
                        }]
                    }
                }
            });
        </script>
        <canvas id="test2"></canvas>
        <script>
            var ctx = document.getElementById('test2').getContext('2d');
            var chart = new Chart(ctx, {
                // The type of chart we want to create
                type: 'bar',

                // The data for our dataset
                data: {
                    labels: [keywordName, '연관 키워드 평균'],
                    datasets: [{
                        label: '검색 키워드',
                        backgroundColor: [
                            'rgb(2, 207, 92)',
                            'rgba(255, 206, 86, 0.5)'],
                        borderColor: [
                            'rgb(2, 207, 92)',
                            'rgba(255, 206, 86, 0.5)'],
                        data: [keywordMedExp, relatedMedExpAvg]
                    }]
                },

                // Configuration options go here
                options: {
                    title: {
                        display: true,
                        text: '평균 입찰가 비교',
                        fontSize: 24,
                        fontColor: 'rgba(46, 49, 49, 1)'
                    },
                    legend: {
                        labels: {
                            fontColor: 'rgba(83, 51, 237, 1)',
                            fontSize: 15
                        }
                    },
                    scales: {
                        xAxes: [{
                            ticks: {
                                fontColor: 'rgba(27, 163, 156, 1)',
                                fontSize: '15'
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontColor: 'rgba(246, 36, 89, 1)',
                                fontSize: '15'
                            }
                        }]
                    }
                }
            });
        </script>
    </div>
</div>

<div style="display: flex; justify-content: space-between; border-top: 2px solid gray; padding: 1% 6%; background-color:white;" >
    <h3><a href="service1.php" style="text-decoration: none; color: black;">개별연구</a></h3>
    <div class="menu" style="display:flex; justify-content:center; align-items: center;">
        <a href="service1.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">키워드 수치 분석</a>
        <a href="service2.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">키워드 추이 분석</a>
        <a href="service3.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">연관 키워드 비교</a>
    </div>
</div>