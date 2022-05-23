package websockets;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.NotificationBean;

import javax.ejb.EJB;
import javax.websocket.*;
import javax.websocket.server.PathParam;
import javax.websocket.server.ServerEndpoint;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

@ServerEndpoint(value = "/notificationsocket/{user}", decoders = MessageDecoder.class, encoders = MessageEncoder.class)
public class NotificationEndpoint {

    private Session session;
    private static List<NotificationEndpoint> notificationEndpoints
            = new ArrayList<>();
    private static HashMap<String, String> users = new HashMap<>();

    @EJB
    private NotificationBean notificationBean;

    @OnOpen
    public void onOpen(
            Session session,
            @PathParam("user") String userId) throws IOException, EncodeException {

        this.session = session;
        if (!notificationEndpoints.contains(this)) {
            notificationEndpoints.add(this);
        }
        users.put(session.getId(), userId);
    }

    @OnMessage
    // message needs to correspond a specific format of string
    //macAddress;emotionName;accuracy;duration;clientEmail
    public void onMessage(Session session, String message)
            throws Exception {
        Message message_ = new Message();
        message_.setContent(message);
        message_.setFrom(users.get(session.getId()));
        String emotion = message.split(";")[1];
        String accuracy = message.split(";")[2];
        String duration = message.split(";")[3];
        String clientEmail = message.split(";")[4];
        String title = "Emotion '"+emotion+"' was detected continuosly!";
        String content = "The elder in your care has been showing "+emotion + " emotions continuously."+
                "\n\nThe '"+emotion+"' values were higher than the defined limit of '"+accuracy+"'" +
                " and these feelings have lasted over the specified duration of "+duration + " seconds." +
                "\n\nPlease make sure to contact your elder and check on his/her health!";
        try{
            notificationBean.create(title,content,clientEmail);
        }catch (Exception e){
            System.out.println(e.getMessage());
        }
        notificationBean.send(clientEmail,title,content);

    }

    @OnClose
    public void onClose(Session session) throws IOException, EncodeException {

        if (notificationEndpoints.contains(this)) {
            notificationEndpoints.remove(this);
        }
        Message message = new Message();
        message.setFrom(users.get(session.getId()));
        message.setContent("Disconnected!");
    }

    @OnError
    public void onError(Session session, Throwable throwable) {
        // Do error handling here
    }
}

