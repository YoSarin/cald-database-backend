#!/bin/bash
token=$(curl -X POST "http://cald/user/login?login=agentP7&password=cXqgXC5o" | jq -r '.token.token')

curl "$@"
