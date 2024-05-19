# Build Go backend
FROM golang:1.20.0-alpine
RUN apk update && apk add alpine-sdk gcc
WORKDIR /src
COPY . .
ENV CGO_ENABLED=1
ENV GOOS=linux
RUN go build -o /bin/server ./main.go
EXPOSE 8080

#Run backend server
ENTRYPOINT ["/bin/server"]