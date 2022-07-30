<style>
    body{
        margin: 0;
    }
    .menu a:hover{
        cursor: pointer;
        color: white;
        background-color: rgb(2, 207, 92);
    }
    .grid line {
        stroke: lightgrey;
        stroke-opacity: 0.7;
    }
    .lineChart {
        fill: none;
        stroke: steelblue;
        stroke-width: 1.5px;
    }
    .lineChart:hover {
        stroke: black;
        stroke-width: 3px;
    }
    .toolTip {
        position: absolute;
        border: 1px solid;
        border-radius: 4px 4px 4px 4px;
        background: rgba(0, 0, 0, 0.8);
        color : white;
        padding: 5px;
        text-align: center;
        font-size: 12px;
        min-width: 30px;
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

<div style="background-color: #F7F9FA; border-bottom: 1px solid gray; padding-top: 2%;" >
    <h1 style="margin-left: 10%;">키워드 추이 분석</h1>
    <div>
        <p style="padding-left: 12%;">검색 키워드를 입력하시면,</p> 
        <p style="margin-top: -14px; padding-left: 12%;">해당 키워드의 1년 간의 월간 검색수 비율을 </p>
        <p style="margin-top: -14px; padding-left: 12%;">그래프로 보여주고 분석합니다.</p>
    <div>
    <form method="POST" style="padding-left: 12%; font-size: 32px;">
        검색 키워드: <input id="api-call-keyword" type="text" name="keyword" style="border: 2px solid rgb(2, 207, 92); border-radius: 10px; font-size: 32px;"/>
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
    
    set_key($targetKeyword, $targetKeyword);
    $targetKeyword = $_POST['keyword'];
    function set_key($key, $data){
        $key = "recent_keyword_input_".$key;
        echo "<script language='Javascript'>localStorage.setItem('$key', '$data');</script>";
    }
    // 네이버 데이터랩 통합검색어 트렌드 Open API 예제
    $client_id = "YOUR_CLIENT_ID"; // 네이버 개발자센터에서 발급받은 CLIENT ID
    $client_secret = "YOUT_CLIENT_SECRET";// 네이버 개발자센터에서 발급받은 CLIENT SECRET
    $url = "https://openapi.naver.com/v1/datalab/search";
    //월별 검색량 추이
    $body = "{\"startDate\":\"2021-07-01\",\"endDate\":\"2022-06-30\",\"timeUnit\":\"month\",\"keywordGroups\":[{\"groupName\":\"$targetKeyword\",\"keywords\":[\"$targetKeyword\"]}]}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = array();
    $headers[] = "X-Naver-Client-Id: ".$client_id;
    $headers[] = "X-Naver-Client-Secret: ".$client_secret;
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // SSL 이슈가 있을 경우, 아래 코드 주석 해제
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $response = curl_exec ($ch);
    $json_response = json_decode($response);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //   echo "status_code:".$status_code." ";
    curl_close ($ch);
?>

<div style="display: flex; justify-content: space-between; align-items: center; padding: 3% 10%; background-color: white;" >
    <table class="monthlySearchTable" id="tableShop" class="table table-bordered table-striped" style="border: 1px solid black; border-collapse:collapse; width: 200px; height: 50px;">
    
        <thead style="border: 1px solid black;">
        <tr>
            <th style='border: 1px solid black;'>기간</th>
            <th style='border: 1px solid black;'>비율</th>
        </tr>
        </thead>
        <tbody id="datalab_monthlySearch_api" style="text-align: center;">
        <?php        
                $jsonArray = (array)($json_response->results[0]);
                $jsonArrayData = $jsonArray["data"];

                $targetPeriods = array();
                $targetRatios = array();

                for($i = 0; $i < count($jsonArrayData); $i++){
                    $tmpArrayData = (array)($jsonArrayData[$i]);
                    $tmpArrayDataPeriod = mb_substr($tmpArrayData["period"], 0,7);
                    $targetPeriods[$i] = $tmpArrayDataPeriod;
                    $targetRatios[$i] = $tmpArrayData["ratio"];
                    
                }
                $ratioSum = array_sum($targetRatios);
                for($i = 0; $i < count($targetRatios); $i++){
                    $targetRatios[$i] = $targetRatios[$i] / $ratioSum;
                    $targetRatios[$i] = round($targetRatios[$i] * 1000) / 10;
                    echo "<tr>";
                    echo "<td style='border: 1px solid black;'>$targetPeriods[$i]</td>";
                    echo "<td style='border: 1px solid black;'>$targetRatios[$i]%</td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
        <tfoot>
        <tr>
            <th style='border: 1px solid black;'>기간</th>
            <th style='border: 1px solid black;'>비율</th>
        </tr>
        </tfoot>
    </table>
    <div style="width: 60%;">
        <h2 style="text-align: center;">월별(x) 검색수 비율(y)</h2>
        <svg width="100%" height="400"></svg>
    </div>
</div>

<div id="searchSummary" style="background-color: white; padding-left: 40px; border-top: 1px dotted black; border-bottom: 1px dotted black;">
    <h3 id="minMaxSummary"></h3>
    <h3 id="averageSummary"></h3>
    <h3 id="recentSummary"></h3>
</div>


<script src="https://d3js.org/d3.v4.min.js"></script>
<script>
    var performance_api_table = document.querySelector(".monthlySearchTable");
    
    var performance_api_table_contents = performance_api_table.childNodes[3];
    let performance_bid = [];
    // let performance_clicks = [];
    let performance_impressions = [];
    // let performance_cost = [];
    
    for(let i = 1; i < performance_api_table_contents.childNodes.length-1; i++){
        performance_bid[i-1] = performance_api_table_contents.childNodes[i].childNodes[0].innerText;
    //     performance_clicks[i-1] = parseInt(performance_api_table_contents[i].childNodes[2].innerText);
        performance_impressions[i-1] = parseFloat(performance_api_table_contents.childNodes[i].childNodes[1].innerText);
    //     performance_cost[i-1] = parseInt(performance_api_table_contents[i].childNodes[4].innerText);
    }
    var dataset = [{'2021-07': performance_impressions[0], '2021-08': performance_impressions[1], '2021-09': performance_impressions[2], '2021-10': performance_impressions[3], '2021-11': performance_impressions[4], '2021-12': performance_impressions[5], '2022-01': performance_impressions[6], '2022-02': performance_impressions[7], '2022-03': performance_impressions[8], '2022-04': performance_impressions[9], '2022-05': performance_impressions[10], '2022-06': performance_impressions[11]}];
    var keys = d3.keys(dataset[0]);
    var data = [];
    
    dataset.forEach(function(d, i) {
    data[i] = keys.map(function(key) { return {x: key, y: d[key]}; })
    });
 
    var margin = {left: 20, top: 10, right: 10, bottom: 20};
    var svg = d3.select("svg");
    var width  = parseInt(svg.style("width"), 10) - margin.left - margin.right;
    var height = parseInt(svg.style("height"), 10)- margin.top  - margin.bottom;
    var svgG = svg.append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
    var xScale = d3.scalePoint()//scaleBand() scaleOrdinal
        .domain(keys)
        .rangeRound([0, width]);
    var yScale = d3.scaleLinear()
        .domain([0, d3.max(dataset, function(d) { return d3.max(keys, function(key) { return d[key];});})])
        .nice()
        .range([height, 0]);
    var colors = d3.scaleOrdinal(d3.schemeCategory10);
 
    svgG.append("g")
        .attr("class", "grid")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(xScale)
            .tickSize(-height)
        );
 
    svgG.append("g")
        .attr("class", "grid")
        .call(d3.axisLeft(yScale)
            .ticks(5)
            .tickSize(-width)
           );
 
    var line = d3.line()
        //.curve(d3.curveBasis)
        .x(function(d) { return xScale(d.x); })
        .y(function(d) { return yScale(d.y); });
    var lineG = svgG.append("g")
        .selectAll("g")
        .data(data)
           .enter().append("g");
 
    lineG.append("path")
        .attr("class", "lineChart")
        // .style("stroke", function(d, i) { return colors( series[i]); })
        .attr("d", function(d, i) {return line(d); });
 
    lineG.selectAll("dot")
        .data(function(d) {return d })
        .enter().append("circle")
            .attr("r", 3)
            .attr("cx", function(d) { return xScale(d.x) })
            .attr("cy", function(d) { return yScale(d.y);})
            .on("mouseover", function() { tooltip.style("display", null); })
            .on("mouseout",  function() { tooltip.style("display", "none"); })
            .on("mousemove", function(d) {
                tooltip.style("left", (d3.event.pageX+10)+"px");
                tooltip.style("top",  (d3.event.pageY-10)+"px");
                tooltip.html("월: " + d.x + "<br/>" + "검색비율: " + d.y +"%");
            });

    var tooltip = d3.select("body")
        .append("div")
        .attr("class", "toolTip")
        .style("display", "none");
 
    var legend = svgG.append("g")
        .attr("text-anchor", "end")
        .selectAll("g")
        // .data(series)
        .enter().append("g")
        .attr("transform", function(d, i) { return "translate(0," + i * 20 + ")"; });
 
    legend.append("rect")
          .attr("x", width - 20)
          .attr("width", 19)
          .attr("height", 19)
        //   .attr("fill", colors);
 
      legend.append("text")
          .attr("x", width - 30)
          .attr("y", 9.5)
          .attr("dy", "0.32em")
          .text(function(d) { return d; });
</script>
<script>
    const divMinMax = document.querySelector("#minMaxSummary");
    let min = 0;
    let max  = 0;
    for(let i = 1; i < performance_impressions.length; i++){
        if(performance_impressions[min] > performance_impressions[i]){
            min = i;
        }
        else if(performance_impressions[max] < performance_impressions[i]){
            max = i;
        }
    }
    divMinMax.innerHTML = `❗ 1년 간 검색수가 가장 많은 달은 <strong style='color: rgb(2, 207, 92);'>${performance_bid[max]}</strong>, 가장 적은 달은 <strong style='color: rgb(2, 207, 92);'>${performance_bid[min]}</strong>입니다.`;
</script>
<script>
    const divAverage = document.querySelector("#averageSummary");
    const average = arr => arr.reduce((p, c) => p + c, 0) / arr.length;
    const averageRound = Math.round(average(performance_impressions) * 10) / 10;
    let roundResult = 0;
    if(performance_impressions[11] >= averageRound){
        roundResult = Math.round((performance_impressions[11] - averageRound)*10)/10;
        divAverage.innerHTML = `❗ 1년 간 해당 키워드의 평균 검색 비율은 <strong style='color: rgb(2, 207, 92);'>${averageRound}%</strong>입니다. 가장 최근 기록인 2022-06의 검색 비율은 평균보다 약 <strong style='color: rgb(2, 207, 92);'>${roundResult}</strong>%p 높습니다.`
    }
    else{
        roundResult = Math.round((averageRound - performance_impressions[11])*10)/10;
        divAverage.innerHTML = `❗ 1년 간 해당 키워드의 평균 검색 비율은 <strong style='color: rgb(2, 207, 92);'>${averageRound}%</strong>입니다. 가장 최근 기록인 2022-06의 검색 비율은 평균보다 약 <strong style='color: rgb(2, 207, 92);'>${roundResult}</strong>%p 낮습니다.`
    }
</script>
<script>
    const divRecent = document.querySelector("#recentSummary");
    let result = "";
    if(performance_impressions[11] >= performance_impressions[10] && performance_bid[10] >= performance_bid[9]){
        result = "❗ 최근 3개월 간 해당 키워드의 검색수는 <strong style='color: rgb(2, 207, 92);'>상승세</strong>에 있습니다."
    }
    else if(performance_impressions[11] <= performance_impressions[10] && performance_impressions[10] <= performance_bid[9]){
        result = "❗ 최근 3개월 간 해당 키워드의 검색수는 <strong style='color: rgb(2, 207, 92);'>하락세</strong>에 있습니다."
    }
    else{
        result = ""
    }
    divRecent.innerHTML = result;
</script>

<div style="display: flex; justify-content: space-between; border-top: 2px solid gray; padding: 1% 6%; background-color:white;" >
    <h3><a href="service1.php" style="text-decoration: none; color: black;">개별연구</a></h3>
    <div class="menu" style="display:flex; justify-content:center; align-items: center;">
        <a href="service1.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">키워드 수치 분석</a>
        <a href="service2.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">키워드 추이 분석</a>
        <a href="service3.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">연관 키워드 비교</a>
    </div>
</div>