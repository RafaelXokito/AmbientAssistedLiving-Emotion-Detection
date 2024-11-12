# Use the official Node.js 18 image as the base
FROM node:18

# Create and set the working directory
WORKDIR /app

# Copy package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application files
COPY . .

# Expose the WebSocket server port
EXPOSE 8080

# Start the server
CMD ["node", "index.js"]
