import urllib2
import json


def call(url):
    req = urllib2.Request('http://cald/' + url)
    req.add_header('X-Auth-Token', "token")
    resp = urllib2.urlopen(req)
    content = json.loads(resp.read())
    return content
