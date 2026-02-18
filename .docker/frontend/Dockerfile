FROM node:20-slim

RUN corepack enable

WORKDIR /app

EXPOSE 3000

CMD ["sh", "-c", "pnpm install && exec pnpm dev --host 0.0.0.0"]
