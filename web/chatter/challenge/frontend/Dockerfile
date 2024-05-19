# Build frontend
FROM node:lts-alpine as frontend-builder
WORKDIR /app
COPY ./chatterbawks-ui/package*.json ./
RUN npm install
COPY ./chatterbawks-ui .
RUN npm run build

FROM nginx:alpine

WORKDIR /usr/share/nginx/html
COPY --from=frontend-builder /app/dist .
EXPOSE 80