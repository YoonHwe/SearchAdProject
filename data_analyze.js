const chart1 = document.querySelector(".circle1");
const chart2 = document.querySelector(".circle2");
const chart3 = document.querySelector(".circle3");
const monthlySearchCountPc = document.querySelector("#monthlySearchCountPc")
const monthlyClickCountPc = document.querySelector("#monthlyClickCountPc")
const monthlyClickRatePc = document.querySelector("#monthlyClickRatePc")
const monthlySearchCountMobile = document.querySelector("#monthlySearchCountMobile")
const monthlyClickCountMobile = document.querySelector("#monthlyClickCountMobile")
const monthlyClickRateMobile = document.querySelector("#monthlyClickRateMobile")

function makeChart(percent, classname, color){
    let i = 1;
    let chartFn = setInterval(function(){
        if(i <=  percent){
            colorFn(i, classname, color);
            i++;
        }
        else{
            clearInterval(chartFn);
        }
    }, 10);
}

function colorFn(i, classname, color){
    classname.style.background = "conic-gradient(" + color + " 0% " + i + "%, #dedede " + i + "% 100%";
}

function replay(){
    makeChart(50, chart1, "#f5b914");
    makeChart(50, chart2, "#0A174E");
    makeChart(50, chart3, "#66d2ce");
}

function getPercentage(data1, data2){
    const data1Num = parseFloat(data1.innerText);
    const data2Num = parseFloat(data2.innerText);

    const percentage = Math.round(data1Num / (data1Num + data2Num) * 100)

    return percentage;
}
getPercentage(monthlySearchCountPc, monthlySearchCountMobile);

makeChart(getPercentage(monthlySearchCountPc, monthlySearchCountMobile), chart1, "#f5b914");
makeChart(getPercentage(monthlyClickCountPc, monthlyClickCountMobile), chart2, "#0A174E");
makeChart(getPercentage(monthlyClickRatePc, monthlyClickRateMobile), chart3, "#66d2ce");