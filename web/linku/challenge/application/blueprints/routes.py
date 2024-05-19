from application.database import register_user_db, login_user_db, update_preferences_db
from flask import Blueprint, render_template, request, redirect, flash, current_app as app
from application.util import response, isAuthenticated
import jwt

web = Blueprint('web', __name__)
api = Blueprint('api', __name__)


@web.route('/', methods=['GET', 'POST'])
def loginView():
    return render_template('login.html')


@web.route('/register', methods=['GET', 'POST'])
def registerView():
    return render_template('register.html')

@web.route('/logout', methods=['GET', 'POST'])
def logoutView():
    resp = redirect("/")
    resp.set_cookie("auth","")
    resp.set_cookie("session","")
    return resp


@web.route('/dashboard', methods=['GET', 'POST'])
@isAuthenticated
def homeView(user):
    return render_template('dashboard.html', username=user["username"],theme=user["theme"])

@web.route('/admin', methods=['GET', 'POST'])
@isAuthenticated
def adminView(user):

    user_level = user.get("user_level","user")

    if user_level != "admin":
        flash("Access Denied.","danger")
        return redirect("/")

    return render_template('admin.html', user=user,flag=app.config["FLAG"])


@api.route('/login', methods=['POST'])
def api_login():

    username = request.form.get('username', '')
    password = request.form.get('password', '')

    if not username or not password:
        flash("All fields are required.","danger")
        return redirect("/")

    claims = login_user_db(username, password)

    if claims:

        token = jwt.encode(claims, app.config['SECRET_KEY'], algorithm="HS256")
        flash("Logged In successfully!","success")
        res = redirect("/dashboard")
        res.set_cookie('auth', token)

        return res

    flash('Invalid credentials.',"danger")
    return redirect("/")


@api.route('/register', methods=['POST'])
def api_register():

    username = request.form.get('username', '')
    password = request.form.get('password', '')

    if not username or not password:
        flash("All fields are required.","danger")
        return redirect("/register")

    user = register_user_db(username, password)

    if user:
        flash("User registered, please log in.","success")
        return redirect("/")

    flash('User already exists!',"danger")
    return redirect("/register")

@api.route('/preferences', methods=['POST'])
@isAuthenticated
def api_update_prefs(user):

    # TODO: Add more configurable preferences
    valid_prefs = ["theme"]

    if not request.is_json:
        return response('Invalid JSON!'), 400

    data = request.get_json()

    if not any([p in data for p in valid_prefs]):
        return response('No valid preferences specified.'), 401

    if update_preferences_db(user["username"],data):
        return response('Preferences updated!'), 200
    
    return response('An error occurred while updating preferences.'), 403

