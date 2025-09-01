# Multi-stage build for React frontend

# Build stage
FROM node:18-alpine AS builder

WORKDIR /app

# Create a non-root user
RUN addgroup -g 1001 -S nodejs
RUN adduser -S nextjs -u 1001

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy source code and set ownership
COPY --chown=nextjs:nodejs . .

# Switch to non-root user
USER nextjs

# Build the application
RUN npm run build

# Production stage
FROM nginx:alpine

# Copy built files from builder stage
COPY --from=builder /app/dist /usr/share/nginx/html

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose port 80
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"]