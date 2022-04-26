package websockets;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import javax.websocket.*;
import javax.websocket.server.PathParam;
import javax.websocket.server.ServerEndpoint;

@ServerEndpoint(value = "/framesocket/{user}", decoders = MessageDecoder.class, encoders = MessageEncoder.class)
public class FrameEndpoint {
    
    private Session session;
    private static List<FrameEndpoint> frameEndpoints 
      = new ArrayList<>();
    private static HashMap<String, String> users = new HashMap<>();

    @OnOpen
    public void onOpen(
      Session session, 
      @PathParam("user") String userId) throws IOException, EncodeException {
 
        this.session = session;
        if (!frameEndpoints.contains(this)) {
            frameEndpoints.add(this);
        }
        users.put(session.getId(), userId);

        Message message = new Message();
        message.setFrom(userId);
        message.setContent("Connected!");
        broadcast(message);
    }

    @OnMessage
    public void onMessage(Session session, String message) 
      throws IOException,EncodeException {
        Message message_ = new Message();
        message_.setContent(message);
        message_.setFrom(users.get(session.getId()));
        broadcast(message_);
    }

    @OnClose
    public void onClose(Session session) throws IOException, EncodeException {
        
        if (frameEndpoints.contains(this)) {
            frameEndpoints.remove(this);
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
 
        frameEndpoints.forEach(endpoint -> {
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
