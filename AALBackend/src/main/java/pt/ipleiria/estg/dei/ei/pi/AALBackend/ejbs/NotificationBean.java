package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import com.sun.tools.javac.util.Pair;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Notification;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;

import javax.annotation.Resource;
import javax.ejb.Stateless;
import javax.mail.Message;
import javax.mail.MessagingException;
import javax.mail.Session;
import javax.mail.Transport;
import javax.mail.internet.InternetAddress;
import javax.mail.internet.MimeMessage;
import javax.persistence.EntityManager;
import javax.persistence.LockModeType;
import javax.persistence.PersistenceContext;
import javax.persistence.TypedQuery;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Objects;

@Stateless
public class NotificationBean {
    @PersistenceContext
    private EntityManager entityManager;

    @Resource(name = "java:/jboss/mail/fakeSMTP")
    private Session mailSession;
    public void send(String to, String subject, String text) throws
            MessagingException {
        Message message = new MimeMessage(mailSession);
        try {
            // Adjust the recipients. Here we have only one recipient.
            // The recipient's address must be an object of the InternetAddress class.
            message.setRecipients(Message.RecipientType.TO,
                    InternetAddress.parse(to, false));
            // Set the message's subject
            message.setSubject(subject);
            // Insert the message's body
            message.setText(text);
            // Adjust the date of sending the message
            Date timeStamp = new Date();
            message.setSentDate(timeStamp);
            // Use the 'send' static method of the Transport class to send themmessage
            Transport.send(message);
        } catch (MessagingException e) {
            throw e;
        }
    }

    /**
     * Finds Emotion by given @Id:name
     * @param name
     * @return
     */
    public Emotion findEmotion(String name) throws Exception{
        Emotion emotion = entityManager.find(Emotion.class, name);
        if(emotion == null){
            throw new MyEntityNotFoundException("[Error] - Emotion with name: \'"+name+"\' not Found");
        }
        return emotion;
    }

    /**
     * Register a new Notification in the System
     * @param title
     * @param content
     * @param clientEmail
     * @return
     * @throws Exception
     */
    public Long create(String title, String content, String clientEmail, String emotionName, Double accuracy, Double duration) throws Exception{
        if(title == null || title.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Title is missing");
        }
        if(content == null || content.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Content is missing");
        }
        Emotion emotion = findEmotion(emotionName);
        if(emotion == null){
            throw new IllegalArgumentException("[Error] - Emotion is missing");
        }
        if(accuracy == null || accuracy < 0){
            throw new IllegalArgumentException("[Error] - Accuracy is missing");
        }
        if(duration == null || duration < 0){
            throw new IllegalArgumentException("[Error] - Duration is missing");
        }
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }
        Notification notification = new Notification(title,content,clientFound,emotion,accuracy,duration);
        emotion.addNotification(notification);

        try {
            entityManager.persist(notification);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }
        notification.getClient().addNotification(notification);
        return notification.getId();
    }

    /**
     * Finds Notification by given @Unique:id
     * @param id
     * @return
     * @throws Exception
     */
    public Notification findNotification(Long id) throws Exception{
        Notification notification = entityManager.find(Notification.class,id);
        if(notification == null){
            throw new MyEntityNotFoundException("[Error] - Notification with id: '"+id+"' not Found");
        }
        return notification;
    }

    /**
     * Gets all the Notifications
     * @return
     */
    public List<Notification> getAllNotifications(String isShort){
        if (Objects.equals(isShort, "yes"))
            return entityManager.createNamedQuery("getAllNotifications", Notification.class).setMaxResults(5).setLockMode(LockModeType.OPTIMISTIC).getResultList();
        else
            return entityManager.createNamedQuery("getAllNotifications", Notification.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     * Gets all the Notifications by client
     * @return
     */
    public List<Notification> getAllNotificationsByClient(String clientEmail, String isShort) throws Exception{
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }
        if (Objects.equals(isShort, "yes"))
            return entityManager.createNamedQuery("getAllNotificationsByClient", Notification.class).setParameter("id", clientFound.getId()).setMaxResults(5).setLockMode(LockModeType.OPTIMISTIC).getResultList();
        else
            return entityManager.createNamedQuery("getAllNotificationsByClient", Notification.class).setParameter("id", clientFound.getId()).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    public List<Pair<String, Long>> getEmotionsWithMostNotificationsByClient(String clientEmail) throws Exception {
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }
        List<Object[]> list = entityManager.createNamedQuery("getEmotionWithTheMostNotificationsByClient").setParameter("id", clientFound.getId()).setLockMode(LockModeType.OPTIMISTIC).getResultList();

        List<Pair<String, Long>> returnVal = new ArrayList<>();
        for (Object[] obj : list) {
            String name = (String) obj[0];
            long count = (long) obj[1];

            returnVal.add(new Pair<>(name, count));
        }

        return returnVal.size() > 0 ? returnVal : null;
    }

    public List<Pair<String, Long>> getEmotionsWithMostNotifications() throws Exception {
        List<Object[]> list = entityManager.createNamedQuery("getEmotionWithTheMostNotifications").setLockMode(LockModeType.OPTIMISTIC).getResultList();
        List<Pair<String, Long>> returnVal = new ArrayList<>();
        for (Object[] obj : list) {
            String name = (String) obj[0];
            long count = (long) obj[1];

            returnVal.add(new Pair<>(name, count));
        }

        return returnVal.size() > 0 ? returnVal : null;
    }

    public List<Pair<String, Long>> getEmotionWithTheLeastNotificationsConfiguredByClient(String clientEmail) throws Exception {
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }

        List<Object[]> list = entityManager.createNamedQuery("getEmotionWithTheLeastNotificationsConfiguredByClient").setParameter("id", clientFound.getId()).setLockMode(LockModeType.OPTIMISTIC).getResultList();
        List<Pair<String, Long>> returnVal = new ArrayList<>();
        for (Object[] obj : list) {
            String name = (String) obj[0];
            long count = (long) obj[1];

            returnVal.add(new Pair<>(name, count));
        }
        return returnVal.size() > 0 ? returnVal : null;
    }

    public List<Pair<String, Long>> getEmotionWithTheLeastNotificationsConfigured() throws Exception {
        List<Object[]> list = entityManager.createNamedQuery("getEmotionWithTheLeastNotificationsConfigured").setLockMode(LockModeType.OPTIMISTIC).getResultList();
        List<Pair<String, Long>> returnVal = new ArrayList<>();
        for (Object[] obj : list) {
            String name = (String) obj[0];
            long count = (long) obj[1];

            returnVal.add(new Pair<>(name, count));
        }
        return returnVal.size() > 0 ? returnVal : null;
    }

    public void updateVisibleStatus(Long id)throws Exception{
        Notification notification = findNotification(id);
        notification.setNotificationSeen(true);
    }

    /***
     * Find Client by given @Unique:Email
     * @param email @Id to find Client
     * @return founded Client or Null if dont
     */
    public Client findClient(String email) {
        TypedQuery<Client> query = entityManager.createQuery("SELECT c FROM Client c WHERE c.email = '" + email + "'", Client.class);
        query.setLockMode(LockModeType.OPTIMISTIC);
        return query.getResultList().size() > 0 ? query.getSingleResult() : null;
    }
}
