const httpServer = require('http').createServer()
const io = require("socket.io")(httpServer, {
    allowEIO3: true,
    cors: {
        origin: "http://localhost:3000",
        methods: ["GET", "POST"],
        credentials: true
    }
})
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
            socket.join("framesocket/"+data.username)
            socket.join("logsocket/"+data.username)
            socket.join("notificationsocket/"+data.username)
            console.log(`Room Users ${data.username} join`)
        }
    })

    socket.on('logged_out', function (data) {
        console.log(`Room User ${data.username} leave`)
        if (data.userType == 'A'){
            socket.leave("framesocket")
            socket.leave("logsocket")
            socket.leave("notificationsocket")
            socket.leave("administrators")
        }
        else{
            socket.leave(data.username)
            socket.leave("framesocket/"+data.username)
            socket.leave("logsocket/"+data.username)
            socket.leave("notificationsocket/"+data.username)
        }
    })

    
    socket.on('newLogMessage', function (data) {
        console.log('newLogMessage',data)
        io.to('logsocket/'+data.userId).emit('newLogMessage', data)
        io.to('logsocket').emit('newLogMessage', data)
    })

    socket.on('newFrameMessage', function (data) {
        console.log('newFrameMessage',data)
        io.to('logsocket/'+data.userId).emit('newLogMessage', data)
        io.to('framesocket/'+data.userId).emit('newFrameMessage', data)
    })
    socket.on('newNotificationMessage', function (data) {
        console.log('newNotificationMessage',data)
        io.to('notificationsocket/'+data.userId).emit('newNotificationMessage', data)
    })
})