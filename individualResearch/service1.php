<style>
    body{
        margin: 0;
    }
    .menu a:hover{
        cursor: pointer;
        color: white;
        background-color: rgb(2, 207, 92);
    }
    .chart-wrap{
        position: relative;
        padding: 5%;
        width: 70%;
        margin: 0 auto;
        margin-top: 40px;
        display: none;
    }
    .circle-graph{
        display: flex;
        justify-content: center;
        gap: 15px;
        padding: 4% 16%;
    }
    .chart{
        position: relative;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        transition: 0.3s;
    }
    .chart1-exp{
        visibility: hidden;
    }
    .circle1:hover .chart1-exp{
        visibility: visible;
    }

    span.center{
        background: #fff;
        display: block;
        position: absolute;
        top: 50%;
        left: 50%;
        width: 160px;
        height: 160px;
        border-radius: 50%;
        text-align: center;
        line-height: 160px;
        font-size: 28px;
        transform: translate(-50%, -50%);
    }

    .tableShop-reverse{
        display:flex;
        display: -webkit-box;
        display: -ms-flexbox;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .tableShop-reverse tbody{
        display:flex
    }

    .tableShop-reverse th{
        display:block;
    }
    .tableShop-reverse td{
        display:block
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

<div style="background-color: #F7F9FA; padding-top: 2%;" >
    <h1 style="margin-left: 10%;">키워드 수치 분석</h1>
    <div>
        <p style="padding-left: 12%;">검색 키워드를 입력하시면,</p> 
        <p style="margin-top: -14px; padding-left: 12%;">해당 키워드의 입찰가 별 예상 노출수를 보여줍니다.</p>
        <p style="margin-top: -14px; padding-left: 12%;">분석하고자 입찰가를 정하여 하단에 입력해 주세요.</p>
    <div>
    <form method="POST" style="padding-left: 12%; font-size: 32px;">
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
         console.log(localStorage.length);
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
    $hintKeywords = $_POST['hintkeyword']; 
    
    function set_key($key, $data){
        $key = "recent_keyword_input_".$key;
        echo "<script language='Javascript'>localStorage.setItem('$key', '$data');</script>";
    }
    set_key($hintKeywords, $hintKeywords);
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
?>

<?php
    error_reporting(0);
    $first_impressions = [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200, 1300, 1400, 1500, 1600, 1700, 1800, 1900, 2000, 2100, 2200, 2300, 2400, 2500, 2600, 2700, 2800, 2900, 3000, 3100, 3200, 3300, 3400, 3500, 3600, 3700, 3800, 3900, 4000,4100, 4200, 4300, 4400, 4500, 4600, 4700, 4800, 4900, 5000];
    $param = array(
        'format' => 'json',
        'device' => 'PC',
        'keywordplus' => true,
        'key' => $hintKeywords,
        'bids' => $first_impressions
    );
    $performance_api = $api->POST('/estimate/performance/keyword', $param);
    debug($tmp_list, $DEBUG);
    $performance_api_estimate = $performance_api['estimate'];
?>

<div style="display: flex; flex-direction: column; justify-content:center; align-items:center">
    <table class="tableShop-reverse" id="tableShop" class="table table-bordered table-striped" style="border: 1px solid black; border-collapse:collapse; width: 200px; height: 50px;">
        <thead style="border: 1px solid black;">
            <tr>
                <th style='border: 1px solid black;'>순서</th>
                <th style='border: 1px solid black;'>입찰가</th>
                <th style='border: 1px solid black;'>예상 클릭수</th>
                <th style='border: 1px solid black;'>예상 노출수</th>
                <th style='border: 1px solid black;'>예상 총비용</th>
            </tr>
        </thead>
        <tbody id="performance_api">
            <?php
                error_reporting(0); 
                $performance_api_estimate_bid = array();
                $performance_api_estimate_clicks = array();
                $performance_api_estimate_impressions = array();
                $performance_api_estimate_cost = array();
                $idx = 1;
                while($idx <= 50){
                    echo "<tr>";
                    echo "<td style='border: 1px solid black;'>$idx</td>";
                    echo "<td style='border: 1px solid black;'>";
                    print_r($performance_api_estimate[$idx-1]['bid']);
                    $performance_api_estimate_bid[$idx-1] = $performance_api_estimate[$idx-1]['bid'];
                    echo "</td>";
                    echo "<td style='border: 1px solid black;'>";
                    print_r($performance_api_estimate[$idx-1]['clicks']);
                    $performance_api_estimate_clicks[$idx-1] = $performance_api_estimate[$idx-1]['clicks'];
                    echo "</td>";
                    echo "<td style='border: 1px solid black;'>";
                    print_r($performance_api_estimate[$idx-1]['impressions']);
                    $performance_api_estimate_impressions[$idx-1] = $performance_api_estimate[$idx-1]['impressions'];
                    echo "</td>";
                    echo "<td style='border: 1px solid black;'>";
                    print_r($performance_api_estimate[$idx-1]['cost']);
                    $performance_api_estimate_cost[$idx-1] = $performance_api_estimate[$idx-1]['cost'];
                    echo "</td>";
                    echo "</tr>";
                    $idx++;
                }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th style='border: 1px solid black;'>순서</th>
                <th style='border: 1px solid black;'>입찰가</th>
                <th style='border: 1px solid black;'>예상 클릭수</th>
                <th style='border: 1px solid black;'>예상 노출수</th>
                <th style='border: 1px solid black;'>예상 총비용</th>
            </tr>
        </tfoot>
    </table>
    <h2>입찰가(x)에 따른 예상 노출수(y)</h2>
    <svg id="svg1" width="90%" height="400"></svg>
    <h2>입찰가(x)에 따른 예상 클릭수(y)</h2>
    <svg id="svg2" width="90%" height="400"></svg>
</div>
<form id="bidApiForm" method="POST" style="padding-left: 12%; padding-top: 2%; font-size: 32px; display: none;">
    분석 입찰가: <input type="text" name="bidAmount" style="border: 2px solid rgb(2, 207, 92); border-radius: 10px; font-size: 32px;" placeholder=" 숫자로 입력 ex. 3000"/>
</form>
<div id="localStorage_bid" style="padding-left: 12%; padding-bottom: 4%; font-size: 16px; border-bottom: 1px solid gray;">
    <span>최근 검색: </span>
</div>
<div class="chart-wrap">
    <div class="circle-graph">
        <div class="chart circle1" style="border: 1px solid black;">
            <span class="center">?</span>
            <h3 style="text-align: center; margin-top: -80px; font-size: 32px;">노출수 대비 클릭수</h3>
            <div class="chart-exp chart1-exp" style="margin-top: 300px;"></div>
        </div>
    </div>
</div>

<div style="display: flex; justify-content: space-between; border-top: 2px solid gray; padding: 1% 6%; background-color:white; margin-top: 4%;" >
    <h3><a href="service1.php" style="text-decoration: none; color: black;">개별연구</a></h3>
    <div class="menu" style="display:flex; justify-content:center; align-items: center;">
        <a href="service1.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">키워드 수치 분석</a>
        <a href="service2.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">키워드 추이 분석</a>
        <a href="service3.php" style="text-decoration: none; color: black; margin: 0px 10px;  border: 1px solid gray; border-radius: 10px; padding: 2px 4px;">연관 키워드 비교</a>
    </div>
</div>

<script src="https://d3js.org/d3.v4.min.js"></script>
<script>
    var performance_api_table = document.querySelector("#performance_api");
    
    var performance_api_table_contents = performance_api_table.childNodes;
    let performance_bid = [];
    let performance_clicks = [];
    let performance_impressions = [];
    let performance_cost = [];
    
    for(let i = 1; i < performance_api_table_contents.length-1; i++){
        performance_bid[i-1] = performance_api_table_contents[i].childNodes[1].innerText;
        performance_clicks[i-1] = parseInt(performance_api_table_contents[i].childNodes[2].innerText);
        performance_impressions[i-1] = parseInt(performance_api_table_contents[i].childNodes[3].innerText);
        performance_cost[i-1] = parseInt(performance_api_table_contents[i].childNodes[4].innerText);
    }
        
    var dataset = [{'1백': performance_impressions[0], '2백': performance_impressions[1], '3백': performance_impressions[2], '4백': performance_impressions[3], '5백': performance_impressions[4], '6백': performance_impressions[5], '7백': performance_impressions[6], '8백': performance_impressions[7], '9백': performance_impressions[8], '10백': performance_impressions[9], '11백': performance_impressions[10], '12백': performance_impressions[11], '13백': performance_impressions[12], '14백': performance_impressions[13], '15백': performance_impressions[14], '16백': performance_impressions[15], '17백': performance_impressions[16], '18백': performance_impressions[17], '19백': performance_impressions[18], '20백': performance_impressions[19], '21백': performance_impressions[20], '22백': performance_impressions[21], '23백': performance_impressions[22], '24백': performance_impressions[23], '25백': performance_impressions[24], '26백': performance_impressions[25], '27백': performance_impressions[26], '28백': performance_impressions[27], '29백': performance_impressions[28], '30백': performance_impressions[29], '31백': performance_impressions[30], '32백': performance_impressions[31], '33백': performance_impressions[32], '34백': performance_impressions[33], '35백': performance_impressions[34], '36백': performance_impressions[35], '37백': performance_impressions[36], '38백': performance_impressions[37], '39백': performance_impressions[38], '40백': performance_impressions[39], '41백': performance_impressions[40], '42백': performance_impressions[41], '43백': performance_impressions[42], '44백': performance_impressions[43], '45백': performance_impressions[44], '46백': performance_impressions[45], '47백': performance_impressions[46], '48백': performance_impressions[47], '49백': performance_impressions[48], '50백': performance_impressions[49]}];
    var dataset2 = [{'1백': performance_clicks[0], '2백': performance_clicks[1], '3백': performance_clicks[2], '4백': performance_clicks[3], '5백': performance_clicks[4], '6백': performance_clicks[5], '7백': performance_clicks[6], '8백': performance_clicks[7], '9백': performance_clicks[8], '10백': performance_clicks[9], '11백': performance_clicks[10], '12백': performance_clicks[11], '13백': performance_clicks[12], '14백': performance_clicks[13], '15백': performance_clicks[14], '16백': performance_clicks[15], '17백': performance_clicks[16], '18백': performance_clicks[17], '19백': performance_clicks[18], '20백': performance_clicks[19], '21백': performance_clicks[20], '22백': performance_clicks[21], '23백': performance_clicks[22], '24백': performance_clicks[23], '25백': performance_clicks[24], '26백': performance_clicks[25], '27백': performance_clicks[26], '28백': performance_clicks[27], '29백': performance_clicks[28], '30백': performance_clicks[29], '31백': performance_clicks[30], '32백': performance_clicks[31], '33백': performance_clicks[32], '34백': performance_clicks[33], '35백': performance_clicks[34], '36백': performance_clicks[35], '37백': performance_clicks[36], '38백': performance_clicks[37], '39백': performance_clicks[38], '40백': performance_clicks[39], '41백': performance_clicks[40], '42백': performance_clicks[41], '43백': performance_clicks[42], '44백': performance_clicks[43], '45백': performance_clicks[44], '46백': performance_clicks[45], '47백': performance_clicks[46], '48백': performance_clicks[47], '49백': performance_clicks[48], '50백': performance_clicks[49]}];
    var keys = d3.keys(dataset[0]);
    var keys2 = d3.keys(dataset2[0])
    var data = [];
    var data2 = []

    dataset.forEach(function(d, i) {
        data[i] = keys.map(function(key) { return {x: key, y: d[key]}; })
    });
    dataset2.forEach(function(d, i){
        data2[i] = keys2.map(function(key2) { return {x: key2, y: d[key2]}; })
    })
    
    var margin = {left: 20, top: 10, right: 10, bottom: 20};
    // var svg = d3.select("svg");
    var svgOne = d3.select("#svg1");
    var svgTwo = d3.select("#svg2");
    var width  = parseInt(svgOne.style("width"), 10) - margin.left - margin.right;
    var height = parseInt(svgOne.style("height"), 10)- margin.top  - margin.bottom;
    var width2  = parseInt(svgTwo.style("width"), 10) - margin.left - margin.right;
    var height2 = (parseInt(svgTwo.style("height"), 10)- margin.top  - margin.bottom);
    
    var svgOneG = svgOne.append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
    var svgTwoG = svgTwo.append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    var xScale = d3.scalePoint()
        .domain(keys)
        .rangeRound([0, width]);
    var xScale2 = d3.scalePoint()
        .domain(keys2)
        .rangeRound([0, width2]);
    var yScale = d3.scaleLinear()
        .domain([0, d3.max(dataset, function(d) { return d3.max(keys, function(key) { return d[key];});})])
        .nice()
        .range([height, 0]);
    var yScale2 = d3.scaleLinear()
        .domain([0, d3.max(dataset2, function(d) { return d3.max(keys2, function(key2) { return d[key2];});})])
        .nice()
        .range([height2, 0]);
    var colors = d3.scaleOrdinal(d3.schemeCategory10);
 
    svgOneG.append("g")
        .attr("class", "grid")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(xScale)
            .tickSize(-height)
        );
    svgOneG.append("g")
        .attr("class", "grid")
        .call(d3.axisLeft(yScale)
            .ticks(5)
            .tickSize(-width)
           );
    
    svgTwoG.append("g")
        .attr("class", "grid")
        .attr("transform", "translate(0," + height2 + ")")
        .call(d3.axisBottom(xScale2)
            .tickSize(-height2)
        );
 
    svgTwoG.append("g")
        .attr("class", "grid")
        .call(d3.axisLeft(yScale2)
            .ticks(5)
            .tickSize(-width2)
           );
 
    var line = d3.line()
        .x(function(d) { return xScale(d.x); })
        .y(function(d) { return yScale(d.y); });
    
    var line2 = d3.line()
        .x(function(d) { return xScale2(d.x); })
        .y(function(d) { return yScale2(d.y); });

    var lineG = svgOneG.append("g")
        .selectAll("g")
        .data(data)
        .enter().append("g");
    var lineG2 = svgTwoG.append("g")
        .selectAll("g")
        .data(data2)
        .enter().append("g");
    
    lineG.append("path")
        .attr("class", "lineChart")
        .attr("d", function(d, i) {return line(d); });
    lineG2.append("path")
        .attr("class", "lineChart")
        .attr("d", function(d, i) {return line2(d); });
    
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
                tooltip.html("입찰가: " + d.x + "<br/>" + "예상 노출수: " + d.y);
            });
    lineG2.selectAll("dot")
        .data(function(d) {return d })
        .enter().append("circle")
            .attr("r", 3)
            .attr("cx", function(d) { return xScale2(d.x) })
            .attr("cy", function(d) { return yScale2(d.y);})
            .on("mouseover", function() { tooltip.style("display", null); })
            .on("mouseout",  function() { tooltip.style("display", "none"); })
            .on("mousemove", function(d) {
                tooltip.style("left", (d3.event.pageX+10)+"px");
                tooltip.style("top",  (d3.event.pageY-10)+"px");
                tooltip.html("입찰가: " + d.x + "<br/>" + "예상 클릭수: " + d.y);
            });

    var tooltip = d3.select("body")
        .append("div")
        .attr("class", "toolTip")
        .style("display", "none");
 
    var legend = svgOneG.append("g")
        .attr("text-anchor", "end")
        .selectAll("g")
        .enter().append("g")
        .attr("transform", function(d, i) { return "translate(0," + i * 20 + ")"; });
 
    legend.append("rect")
          .attr("x", width - 20)
          .attr("width", 19)
          .attr("height", 19)
 
    legend.append("text")
          .attr("x", width - 30)
          .attr("y", 9.5)
          .attr("dy", "0.32em")
          .text(function(d) { return d; });
    
    var legend2 = svgTwoG.append("g")
        .attr("text-anchor", "end")
        .selectAll("g")
        .enter().append("g")
        .attr("transform", function(d, i) { return "translate(0," + i * 20 + ")"; });
 
    legend2.append("rect")
          .attr("x", width2 - 20)
          .attr("width", 19)
          .attr("height", 19)
 
    legend2.append("text")
          .attr("x", width2 - 30)
          .attr("y", 9.5)
          .attr("dy", "0.32em")
          .text(function(d) { return d; });

    const tableShopReverse = document.querySelector(".tableShop-reverse");
    tableShopReverse.style.visibility = "hidden";
    const bidApiForm = document.querySelector("#bidApiForm");
    bidApiForm.style.display = "block";
</script>

<script>
    const bidApiInput = bidApiForm.querySelector("input");

    bidApiForm.addEventListener("submit", calculRatios);

    function calculRatios(event){
        event.preventDefault();
        
        const bidInputString = bidApiInput.value;
        const bidInputInt = parseInt(bidInputString);
        const RECENT_BID_INPUT = `recent_bid_input_${bidInputString}`
        localStorage.setItem(RECENT_BID_INPUT, bidInputInt);

        let bidInputForDict = "";
        if(bidInputString.length == 4){
            bidInputForDict = bidInputString[0] + bidInputString[1] + "백";
        } 
        else{
            bidInputForDict = bidInputString[0] + "백";
        }
        const targetImpressions = dataset[0][bidInputForDict];
        const targetClk = dataset2[0][bidInputForDict];

        const clkRatio = Math.round((targetClk / targetImpressions) * 10000) / 100;
        const chartWrap = document.querySelector(".chart-wrap");
        chartWrap.style.display = "block";
        const chart1 = document.querySelector(".circle1");
        const chart1Percent = chart1.querySelector(".center");
        
        const chart1Exp = chart1.querySelector(".chart-exp");
        expClkPercent = parseInt(100*clkRatio);
        chart1Exp.innerHTML = `<h4 style='text-align:center;'>예상 노출 수:${targetImpressions}, 예상 클릭수: ${targetClk}</h4>10,000명에게 노출됐을 때 ${expClkPercent} 명이 클릭할 것입니다.`
        
        chart1Percent.innerText = `${clkRatio}%`;

        makeChart(clkRatio, chart1, "#02cf5c");

    }
    
    function makeChart(percent, classname, color){
        let i = 1;
        let chartFn = setInterval(function(){
            if(i <=  percent){
                colorFn(i, classname, color);
                i++;
            }
            else{
                colorFn(percent, classname, color);
            }
        }, 10);
    }

    function colorFn(i, classname, color){
        classname.style.background = "conic-gradient(" + color + " 0% " + i + "%, #dedede " + i + "% 100%";
    }
</script>
<script>
    const localStorageBid = document.querySelector("#localStorage_bid");
    for(let i = 0; i < localStorage.length-1; i++){
        if(localStorage.key(i).includes('recent_bid')){
            const spanForBid = document.createElement("span");
        
            tmpLocal = localStorage.getItem(localStorage.key(i));
            spanForBid.innerText = tmpLocal;
            spanForBid.style.border = "1px solid black";
            spanForBid.style.padding = "2px 4px";
            spanForBid.style.margin = "0px 2px";
            spanForBid.style.borderRadius = "10px";
            localStorageBid.appendChild(spanForBid);
        }
    }
</script>