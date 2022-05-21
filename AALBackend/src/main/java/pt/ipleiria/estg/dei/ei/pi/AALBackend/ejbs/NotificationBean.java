package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;
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
import java.util.Date;
import java.util.List;

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
     * Register a new Notification in the System
     * @param title
     * @param content
     * @param clientEmail
     * @return
     * @throws Exception
     */
    public Long create(String title, String content, String clientEmail) throws Exception{
        if(title == null || title.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Title is missing");
        }
        if(content == null || content.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Content is missing");
        }
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }
        Notification notification = new Notification(title,content,clientFound);

        try {
            entityManager.persist(notification);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException(ex.getMessage());//"Error persisting your data"
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
    public List<Notification> getAllNotifications(){
        return entityManager.createNamedQuery("getAllNotifications", Notification.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     * Gets all the Notifications by client
     * @return
     */
    public List<Notification> getAllNotificationsByClient(String clientEmail) throws Exception{
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }
        return clientFound.getNotifications();
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
