# Use a base image with Node.js installed
FROM node:16

# Set the working directory inside the container
WORKDIR /app

# Copy package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of your app's source code
COPY . .

# Build your application
RUN npm run build

# Install 'serve' to serve your static files
RUN npm install -g serve

# Expose the port the app runs on
EXPOSE 80

# Serve your static files
CMD ["serve", "dist", "-l", "80"]
