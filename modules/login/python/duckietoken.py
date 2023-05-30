import json
import sys
import traceback


def exit_with_json(exit_code, message, data=None):
    print(json.dumps({"exit_code": exit_code, "message": message, "data": data}))
    exit(exit_code)


try:
    from dt_authentication import DuckietownToken, InvalidToken

    # Return codes:
    #   0 : OK
    #   1 : No token passed as input
    #   2 : Invalid token
    #   3 : Token expired
    #   4 : Not enough scope
    #  99 : Generic error
    # -------------------------------------------------

    if __name__ == "__main__":
        # make sure we received a token
        if len(sys.argv) != 2:
            exit_with_json(1, "Duckietown Token not provided")
        # get token
        token = None
        token_str = sys.argv[1]
        try:
            token = DuckietownToken.from_string(token_str)
        except InvalidToken:
            exit_with_json(2, "Duckietown Token not valid")
        except:
            exit_with_json(99, "Generic error")
        # make sure the token has not expired
        if token.expired:
            exit_with_json(3, f"The token you used reached its expiration date ({token.expiration} UTC). "
                              f"Get a new token to continue.")
        # make sure the token has enough scope
        if not token.grants("auth"):
            exit_with_json(4, "This token does not grant the scope 'auth'. Get a new token to continue.")
        # ---
        exit_with_json(0, "OK", {"uid": token.uid})
except Exception:
    exit_with_json(99, traceback.format_exc())
