#! python
from lib import call

playerID = 129
data = call('player/%s/history' % playerID)
print(data)
