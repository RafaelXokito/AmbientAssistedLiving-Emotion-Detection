package websockets;

import javax.ejb.EJB;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import javax.websocket.*;
import javax.websocket.server.PathParam;
import javax.websocket.server.ServerEndpoint;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.LogBean;

@ServerEndpoint(value = "/logsocket/{user}", decoders = MessageDecoder.class, encoders = MessageEncoder.class)
public class LogEndpoint {
    
    private Session session;
    private static List<LogEndpoint> logEndpoints 
      = new ArrayList<>();
    private static HashMap<String, String> users = new HashMap<>();

    @EJB
    private LogBean logBean;

    @OnOpen
    public void onOpen(
      Session session, 
      @PathParam("user") String userId) throws IOException, EncodeException {
 
        this.session = session;
        if (!logEndpoints.contains(this)) {
            logEndpoints.add(this);
        }
        users.put(session.getId(), userId);

        Message message = new Message();
        message.setFrom(userId);
        message.setContent("Connected!");
        broadcast(message);
    }

    @OnMessage
    // message needs to correspond a specific format of string
    //macAddress;process;content;clientEmail
    public void onMessage(Session session, String message) 
      throws Exception {
        Message message_ = new Message();
        message_.setContent(message);
        message_.setFrom(users.get(session.getId()));

        logBean.create(message.split(";")[0], message.split(";")[1], message.split(";")[2], message.split(";")[3]);
        
        broadcast(message_);
    }

    @OnClose
    public void onClose(Session session) throws IOException, EncodeException {
        
        if (logEndpoints.contains(this)) {
            logEndpoints.remove(this);
        }
        Message message = new Message();
        message.setFrom(users.get(session.getId()));
        message.setContent("Disconnected!");
        broadcast(message);
    }

    @OnError
    public void onError(Session session, Throwable throwable) {
        // Do error handling here
    }

    private static void broadcast(Message message) 
      throws IOException, EncodeException {
 
        logEndpoints.forEach(endpoint -> {
            synchronized (endpoint) {
                try {
                    endpoint.session.getBasicRemote().
                      sendObject(message);
                } catch (IOException | EncodeException e) {
                    e.printStackTrace();
                }
            }
        });
    }

}
