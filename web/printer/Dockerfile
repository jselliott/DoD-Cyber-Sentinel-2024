FROM python:3-alpine

# Install packages
RUN apk add --update --no-cache supervisor git

# Upgrade pip
RUN python -m pip install --upgrade pip

# Install dependencies
RUN pip install gunicorn Flask pyjwt

# Copy flag
COPY flag.txt /flag.txt
COPY password.txt /password.txt

# add user
RUN adduser -D -u 1000 -g 1000 -s /usr/sbin/nologin www

# Switch working environment
WORKDIR /app

# Add application
COPY challenge .

# Setup supervisor
COPY config/supervisord.conf /etc/supervisord.conf

# Expose port the server is reachable on
EXPOSE 1337

# Disable pycache
ENV PYTHONDONTWRITEBYTECODE=1

# Run supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]