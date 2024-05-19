# Build frontend
FROM node:lts-alpine as frontend-builder
WORKDIR /app
COPY ./challenge/frontend/chatterbawks-ui/package*.json ./
RUN npm install
COPY ./challenge/frontend/chatterbawks-ui .
RUN npm run build

# Build Go backend
FROM golang:1.20.0-alpine as backend-builder
RUN apk update && apk add alpine-sdk gcc
WORKDIR /src
COPY ./challenge/backend .
ENV CGO_ENABLED=1
ENV GOOS=linux
RUN go build -o /bin/server ./main.go

FROM alpine:latest
RUN apk update && apk add --no-cache supervisor nginx
COPY ./config/nginx.conf /etc/nginx/http.d/default.conf
COPY ./config/supervisord.conf /etc/supervisord.conf
WORKDIR /app
COPY --from=frontend-builder /app/dist /app/frontend
COPY --from=backend-builder /bin/server /app/server
RUN chmod +x server

EXPOSE 80

#Run backend server with supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]