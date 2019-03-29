import os
import sys
import json
import base58
import ecdsa
from ecdsa import VerifyingKey, BadSignatureError


PUBLIC_KEY = \
"""-----BEGIN PUBLIC KEY-----
MEkwEwYHKoZIzj0CAQYIKoZIzj0DAQEDMgAEQr/8RJmJZT+Bh1YMb1aqc2ao5teE
ixOeCMGTO79Dbvw5dGmHJLYyNPwnKkWayyJS
-----END PUBLIC KEY-----"""


class DuckietownToken(object):
  VERSION = 'dt1'

  def __init__(self, payload, signature):
    self.payload = payload
    self.signature = signature

  @staticmethod
  def from_string(s):
    p = s.split('-')
    if len(p) != 3:
      raise ValueError(p)
    if p[0] != DuckietownToken.VERSION:
      raise ValueError(p[0])
    payload_base58 = p[1]
    signature_base58 = p[2]
    payload = base58.b58decode(payload_base58)
    signature = base58.b58decode(signature_base58)
    return DuckietownToken(payload, signature)

def exit_with_json(exit_code, message, data=None):
  print(json.dumps({
    'exit_code': exit_code,
    'message': message,
    'data': data
  }))
  exit(exit_code)

# Return codes:
#   0 : OK
#   1 : No token passed as input
#   2 : Invalid token
#   3 : Bad signature
#-------------------------------------------------

if __name__ == '__main__':
  # make sure we received a token
  if len(sys.argv) != 2:
    message = 'Duckietown Token not provided'
    exit_with_json(1, message)
  # get (alleged) token
  token_str = sys.argv[1]
  bad_token_message = 'Duckietown Token not valid'
  try:
    token = DuckietownToken.from_string(token_str)
  except ValueError as e:
    exit_with_json(2, bad_token_message)
  # ---
  vk = VerifyingKey.from_pem(PUBLIC_KEY)
  is_valid = False
  try:
    # verify token
    is_valid = vk.verify(token.signature, token.payload)
  except BadSignatureError as e:
    exit_with_json(2, bad_token_message)
  # ---
  if not is_valid:
    exit_with_json(3, bad_token_message)
  # ---
  data = json.loads(token.payload.decode("utf-8"))
  if not isinstance(data, dict) or 'uid' not in data:
    exit_with_json(3, bad_token_message)
  # ---
  exit_with_json(0, 'OK', data)
