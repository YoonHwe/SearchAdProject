import time
import random
import requests

import signaturehelper


def get_header(method, uri, api_key, secret_key, customer_id):
    timestamp = str(round(time.time() * 1000))
    signature = signaturehelper.Signature.generate(timestamp, method, uri, SECRET_KEY)
    return {'Content-Type': 'application/json; charset=UTF-8', 'X-Timestamp': timestamp, 'X-API-KEY': API_KEY, 'X-Customer': str(CUSTOMER_ID), 'X-Signature': signature}


BASE_URL = 'https://api.naver.com'
API_KEY = '01000000003b115e7c398acfeb7490f3b402d4c59d7f0f55f47a21d32181d03d5e1e00cb39'
SECRET_KEY = 'AQAAAAA7EV58OYrP63SQ87QC1MWdtmaeF9FG+RlckfOd2tevGw=='
CUSTOMER_ID = 2522954

#### 연관 키워드 추출
# uri  ='/keywordstool'
# method = 'GET'

# r = requests.get(BASE_URL + uri + '?hintKeywords={}&showDetail=1'.format(input('연관키워드를 조회할 키워드를 입력하세요\n')),
#                  headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))

# print("response status_code = {}".format(r.status_code))
# # print("response body = {}".format(r.json()))
# print("====================")

# print(r.json()['keywordList'][0])
# for i in range(20):
#     print(r.json()['keywordList'][i]['relKeyword'])

##estimate
uri = '/estimate/performance/keyword'
method = 'POST'
# r = requests.post(BASE_URL + uri, json={
#      "device":"PC", "keywordplus":True, "keyword":"진공청소기", "bids":[50, 100, 150]
# }, headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))
# r = requests.post(BASE_URL + uri, json={
#      "device":"PC",  "keyword":"진공청소기", "bids":[50, 100, 150]
# }, headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))

# r = requests.post(BASE_URL + uri, json={
    
#     'device': 'PC', 
#     'keywordPlus': True,
#     'key': '진공청소기',
#     'bids': [10, 20, 30]
#     }, headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))
# print("response status_code = {}".format(r.status_code))
# print("response body = {}".format(r.json()))
print("====================")

## PC 기준 키워드 입찰가 현황(1~15위)
# uri = '/estimate/average-position-bid/keyword'
# method = 'POST'
# r = requests.post(BASE_URL + uri, json={'device': 'PC', 'items': [{'key': '진공청소기', 'position': 1}, {'key': '진공청소기', 'position': 2}, {'key': '진공청소기', 'position': 3}, {'key': '진공청소기', 'position': 4}, {'key': '진공청소기', 'position': 5}, {'key': '진공청소기', 'position': 6}, {'key': '진공청소기', 'position': 7}, {'key': '진공청소기', 'position': 8}, {'key': '진공청소기', 'position': 9}, {'key': '진공청소기', 'position': 10}, {'key': '진공청소기', 'position': 11}, {'key': '진공청소기', 'position': 12}, {'key': '진공청소기', 'position': 13}, {'key': '진공청소기', 'position': 14}, {'key': '진공청소기', 'position': 15}]}, headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))
# print("response body: = {}".format(r.json())) 

## 키워드의 입찰가 중앙값
# uri = '/estimate/median-bid/keyword'
# method = 'POST'
# r = requests.post(BASE_URL + uri, json={'device': 'PC', 'period': 'MONTH','items': ['진공청소기']}, headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))
# print("response body: = {}".format(r.json())) 

## 키워드의 최소 노출 입찰가
# uri = '/estimate/exposure-minimum-bid/keyword'
# method = 'POST'
# r = requests.post(BASE_URL + uri, json={'device': 'PC', 'period': 'MONTH','items': ['진공청소기']}, headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))
# print("response body: = {}".format(r.json())) 

# 키워드의 월간 예상 실적(복수 금액 가능)
# bid: 입찰가(최대 100개까지), clicks: 예상 클릭수, impressions: ?, cost: 예상비용
# keyword plus 시 impression +72
input_keyword = '진공청소기'
first_bid_settings = list(range(100, 5001, 100))
# print(first_bid_settings)
uri = '/estimate/performance/keyword'
method = 'POST'
r = requests.post(BASE_URL + uri, json={'device': 'PC', 'keywordplus': False, 'key': input_keyword,'bids': first_bid_settings}, headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))
print("response body: = {}".format(r.json()))
expected_clks = []
for i in range(50):
    expected_clk = r.json()['estimate'][i]['clicks']
    expected_clks.append(expected_clk)
    print("입찰가: ", first_bid_settings[i], " / 예상 클릭수: ", expected_clk)
print("======================================")
##사용자가 키워드 입찰가를 3000원으로 설정 했다고 가정
selected_bid_idx = 29
print("선택된 입찰가: ", first_bid_settings[selected_bid_idx])
print("예상 총 비용: ", r.json()['estimate'][selected_bid_idx]['cost'])

uri  ='/keywordstool'
method = 'GET'

r = requests.get(BASE_URL + uri + '?hintKeywords={}&showDetail=1'.format(input_keyword),
                 headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))

# print("response body = {}".format(r.json()))
print(r.json()['keywordList'][0])

qcPcCnt = r.json()['keywordList'][0]['monthlyPcQcCnt'] #월간 검색수 PC
qcMobileCnt = r.json()['keywordList'][0]['monthlyMobileQcCnt'] #월간 검색수 Mobile
clkPcCnt = r.json()['keywordList'][0]['monthlyAvePcClkCnt'] #월간 클릭수 PC
clkMobileCnt = r.json()['keywordList'][0]['monthlyAveMobileClkCnt'] #월간 클릭수 Mobile

print("월간 검색 수 대비 예상 노출 수 비율: ")
print("예상 노출 수 대비 예상 클릭 수 비율: ")

## 키워드의 월간 예상 실적(복수 키워드 가능)
## bid: 입찰가, clicks: 예상 클릭수, impressions: ?, cost: 예상비용
# uri = '/estimate/performance-bulk'
# method = 'POST'
# r = requests.post(BASE_URL + uri, json={'items': [{'device': 'PC', 'keywordplus': True, 'keyword': '진공청소기', 'bid': 1000}, {'device': 'PC', 'keywordplus': True, 'keyword': '무선청소기', 'bid': 2000}]}, headers=get_header(method, uri, API_KEY, SECRET_KEY, CUSTOMER_ID))
# print("response body: = {}".format(r.json()))