from flask import Flask, render_template, request, jsonify, make_response, redirect
from logging.config import dictConfig
import jwt
from functools import wraps

dictConfig({
    'version': 1,
    'formatters': {'default': {
        'format': '[%(asctime)s] %(levelname)s in %(module)s: %(message)s',
    }},
    'handlers': {'wsgi': {
        'class': 'logging.StreamHandler',
        'stream': 'ext://flask.logging.wsgi_errors_stream',
        'formatter': 'default'
    }},
    'root': {
        'level': 'INFO',
        'handlers': ['wsgi']
    }
})

app = Flask(__name__)
app.config.from_object('application.config.Config')

flag = open("/flag.txt").read()
dev_password = open("/password.txt").read()


def token_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):

        token = request.cookies.get('admin_token')

        if not token:
            return redirect("/?error=Access%20Denied")

        try:
            # Decode the token using our secret key
            data = jwt.decode(token, app.config['SECRET_KEY'], algorithms=["HS256"])

            if data["username"] != "dev":
                return redirect("/?error=Access%20Denied")

        except:
            return redirect("/?error=Access%20Denied")

        return f(*args, **kwargs)

    return decorated

@app.route('/',methods=['GET'])
def index():
    return render_template("index.html")

@app.route('/api/login',methods=['POST'])
def login():

    J = request.get_json()

    if J is None:
        return make_response(jsonify({"error":"Invalid JSON Request"}))

    g = J.get('password',None)

    if g is None:
        return make_response(jsonify({"error":"No Password Provided"}))

    if g == dev_password:
        resp = make_response(jsonify({"success":"Login Successful!"}))

        token = jwt.encode(
            {"username": "dev"},
            app.config["SECRET_KEY"],
            algorithm="HS256"
        )

        resp.set_cookie('admin_token', token)

        return resp
    else:
        return make_response(jsonify({"error":"Incorrect Password"}))

@app.route('/admin',methods=['GET'])
@token_required
def recent():
    return render_template("admin.html",flag=flag)

@app.route('/notes.txt',methods=['GET'])
def notes():
    resp = make_response("TODO: Finish implementing user database. Dev password is '%s'." % dev_password)
    resp.content_type = "text/plain"
    return resp

@app.route('/robots.txt')
def send_robots():
    resp = make_response("User-agent: *\nDisallow: /notes.txt")
    resp.content_type = "text/plain"
    return resp
        
        
    



