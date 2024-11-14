const httpServer = require('http').createServer()
const io = require("socket.io")(httpServer, {
    allowEIO3: true,
    cors: {
        origin: "*",
        methods: ["GET", "POST"],
        credentials: true
    }
})

require('dotenv').config();

// Now you can access the environment variables via process.env
const apiUrl = process.env.API_URL;
console.log(`Laravel API URL: ${apiUrl}`)

httpServer.listen(8081, function () {
    console.log('listening on *:8081')
})
io.on('connection', function (socket) {
    console.log(`client ${socket.id} has connected`)

    socket.on('logged_in', function (data) {
        if (data.userType == 'A') {
            socket.join("logsocket")
            socket.join("administrators")
            console.log(`Room Administrator ${data.username} join`)
        }
        else{
            socket.join("message/"+data.username)
            socket.join("notificationsocket/"+data.username)
            console.log(`Room Users ${data.username} join`)
        }
    })

    socket.on('logged_out', function (data) {
        console.log(`Room User ${data.username} leave`)
        if (data.userType == 'A'){
            socket.leave("notificationsocket")
            socket.leave("administrators")
        }
        else{
            socket.leave(data.username)
            socket.leave("notificationsocket/"+data.username)
        }
    })

    socket.on('newNotificationMessage', function (data) {
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
        
        fetch(apiUrl + "/notifications", {
            method: "POST",
            headers: headers,
            body: JSON.stringify(data)
        })
        .then(() => {
            console.log('notification saved, now sending to subscribers');
            io.to('notificationsocket/'+data.userId).emit('newNotificationMessage', data);
        })
        .catch(error => {
            console.log('Error in saving notification: ' + error);
        });
        
        
    })
    
})