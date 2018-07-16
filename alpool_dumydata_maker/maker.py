import csv
from random import randint
import os
os.remove("data.csv")

f = open('data.csv', 'a', encoding='utf-8', newline='\n')
wr = csv.writer(f)
wr.writerow([
  "ID",
  "Title",
  "Content",
  "Excerpt",
  "Date",
  "Post Type",
  "Permalink",
  "Image URL",
  "Image Title",
  "Image Caption",
  "Image Description",
  "Image Alt Text",
  "Attachment URL",
  "Question Categories",
  "Question Tags",
  "Status",
  "Author ID",
  "Author Username",
  "Author Email",
  "Author First Name",
  "Author Last Name",
  "Slug",
  "Format",
  "Template",
  "Parent",
  "Parent Slug",
  "Order",
  "Comment Status",
  "Ping Status",
  "Post Modified Date",
  "answers",
  "selected",
  "votes_up",
  "votes_down",
  "views",
  "year",
  "session",
  "roles",
  "inspection_check"
])
categories = {
  "화공열역학" : "자격증>국가기술>화공기사>화공열역학",
  "단위조작-및-화학공업양론" : "자격증>국가기술>화공기사>단위조작-및-화학공업양론",
  "일반화학" : "자격증>국가기술>위험물산업기사>일반화학",
  "위험물의-성질과-취급" : "자격증>국가기술>위험물산업기사>위험물의-성질과-취급",
  "인간공학-및-시스템안전공학" : "자격증>국가기술>산업안전기사>인간공학-및-시스템안전공학",
  "안전관리론" : "자격증>국가기술>산업안전기사>안전관리론",
  "소방전기시설의-구조-및-원리" : "자격증>국가기술>소방설비기사>소방전기시설의-구조-및-원리",
  "소방원론" : "자격증>국가기술>소방설비기사>소방원론",
  "판매관리" : "자격증>국가기술>텔레마케팅관리사>판매관리",
  "시장조사" : "자격증>국가기술>텔레마케팅관리사>시장조사",
  "행정법" : "자격증>국가전문>기술행정사>행정법",
  "민법" : "자격증>국가전문>기술행정사>민법",
  "사무관리론" : "자격증>국가전문>기술행정사>사무관리론",
  "소방안전관리론" : "자격증>국가전문>소방시설관리사>소방안전관리론",
  "소방시설의-점검실무-행정" : "자격증>국가전문>소방시설관리사>소방시설의-점검실무-행정",
  "소방관련법령" : "자격증>국가전문>소방시설관리사>소방관련법령",
  "정보시스템감리사" : "자격증>민간-및-기타>정보시스템감리사",
  "영어" : "공무원>9급>영어",
  "컴퓨터" : "공무원>9급>컴퓨터",
  "한국사" : "공무원>9급>한국사",
  "세법" : "공무원>7급>세법",
  "7급-민법" : "공무원>7급>7급-민법",
  "형법" : "공무원>7급>형법",
}
years = [
  2013,
  2014,
  2015,
  2016,
  2017,
  2018
]
sessions = [
  1,
  2,
  3,
  4
]


the_id = 300
for category_name, full_category_name in categories.items():
  for year in years:
    for session in sessions:
      the_id = the_id+1
      title = "%s %d년 %d회차 질문 - %d[채택X][답변X]"%(category_name, year, session, the_id)
      slug = "%s-%d년-%d회차-질문-%d채택x답변x/"%(category_name, year, session, the_id)
      permalink = "http://localhost:8888/site/question/%s"%(slug)
      wr.writerow([
        the_id, # id
        title, # title
        "이것은 테스트 질문 내용입니다", # content
        None, # Excerpt
        "2018-03-19", # Date
        "question", # Post Type
        permalink, # Permalink
        None, # Image URL
        None, # Image Title
        None, # Image Caption
        None, # Image Description
        None, # Image Alt Text
        None, # Attachment URL
        full_category_name, # Question Categories
        None, # Question Tags
        "publish", # Status
        1, # Author ID
        "rpf5573", # Author Username
        "rpf5573@gmail.com", # Author Email
        None, # Author First Name
        None, # Author Last Name
        slug, # Slug
        None, # Format
        "default", # Template
        0, # Parent
        0, # Parent Slug
        0, # Order
        "open", # Comment Status
        "open", # Ping Status
        "2018-03-19", # Post Modified Date
        randint(0, 4), # answers
        0, # selected
        randint(0, 20), # votes_up
        randint(0, 10), # votes_down
        0, # views
        year, # year
        session, # 회차
        1, # roles
        randint(0, 1), # inspection_check
      ])
f.close()