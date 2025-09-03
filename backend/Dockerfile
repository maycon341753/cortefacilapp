# Use Node.js 18 LTS
FROM node:18-alpine

# Install SSH client, bash and sshpass
RUN apk add --no-cache openssh-client bash sshpass

# Set working directory
WORKDIR /app

# Copy package files
COPY server/package*.json ./

# Install dependencies
RUN npm install --production

# Copy application code
COPY server/ .

# Create non-root user
RUN addgroup -g 1001 -S nodejs
RUN adduser -S nodejs -u 1001

# Change ownership of the app directory
RUN chown -R nodejs:nodejs /app
USER nodejs

# Expose port
EXPOSE 3001

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node healthcheck.js || exit 1

# Copy SSH tunnel setup script
COPY ssh-tunnel-setup.sh ./
RUN chmod +x ssh-tunnel-setup.sh

# Start the application with SSH tunnel
CMD ["sh", "-c", "./ssh-tunnel-setup.sh && npm start"]