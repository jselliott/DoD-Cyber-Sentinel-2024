from flask import jsonify, abort, request, flash, redirect, current_app as app
from functools import wraps
import jwt
import logging

def response(message):
    return jsonify({'message': message})

def isAuthenticated(f):
    @wraps(f)
    def decorator(*args, **kwargs):
        token = request.cookies.get('auth', False)
        if not token:
            flash("You must be logged in to access this page.","danger")
            return redirect("/")
        try:
            data = jwt.decode(token,app.config['SECRET_KEY'], algorithms=["HS256"])
            kwargs['user'] = data
            return f(*args, **kwargs)
        except Exception as e:
            flash("You must be logged in to access this page.","danger")
            return redirect("/")
    return decorator

