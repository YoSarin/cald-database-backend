#!/usr/bin/python

import argparse
import requests
import os
from urllib.parse import parse_qsl

config = {
    "int" : {
        "domain": "cald.yosarin.net"
    },
    "prod" : {
        "domain": "api.evidence.czechultimate.cz"
    },
    "local" : {
        "domain": "172.17.0.3"
    },
}

user = os.environ["CALD_USER"]
password = os.environ["CALD_PASS"]

def createParser():
    parser = argparse.ArgumentParser(description="perform API calls")
    parser.add_argument('--production', action="store_true", help="if calls shall be done at prod env")
    parser.add_argument('--local', action="store_true", help="if calls shall be done at localhost env")
    parser.add_argument('--api')
    parser.add_argument('--post', action="store_true")
    parser.add_argument('--put', action="store_true")
    parser.add_argument('--delete', action="store_true")
    parser.add_argument('--token')
    parser.add_argument('--data', type=parseData, default={})
    return parser

def parseData(data):
    tuples = parse_qsl(data)
    return { t[0]:t[1] for t in tuples }

def getToken(domain):
    response = requests.post("https://%s/user/login" % domain, data={"login":user, "password":password})
    return response.json()["token"]["token"]
    
def callApi(method, domain, url, token, data={}):
    data["token"] = token
    response = requests.request(method, "https://%s/%s" % (domain, url), data=data)
    return response.content

if __name__ == "__main__":
    args = createParser().parse_args()
    
    token = args.token
    domain = config["int"]["domain"]
    if args.production:
        domain = config["prod"]["domain"]
    if args.local:
        domain = config["local"]["domain"]
        if token == None:
            token = "token"
    
    method = "GET"
    if args.post:
        method = "POST"
    elif args.put:
        method = "PUT"
    elif args.delete:
        method = "DELETE"
    
    if token == None:    
        token = getToken(domain)
        
    response = callApi(method, domain, args.api, token, args.data)
    print(response.decode())
