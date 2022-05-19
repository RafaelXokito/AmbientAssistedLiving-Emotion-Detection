package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.EmotionNotification;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityExistsException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;

import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.LockModeType;
import javax.persistence.PersistenceContext;
import javax.persistence.TypedQuery;
import java.time.Duration;
import java.util.List;

@Stateless
public class EmotionNotificationBean {
    @PersistenceContext
    private EntityManager entityManager;

    /**
     * Register a Emotion for notifications for a client
     * @param accuracyLimit
     * @param duration_seconds
     * @param emotionName
     * @param clientEmail
     * @return
     * @throws Exception
     */
    public Long create(Double accuracyLimit, long duration_seconds, String emotionName, String clientEmail) throws Exception{
        if(emotionExistsClientEmotion(clientEmail,emotionName)){
            throw new MyEntityExistsException("[Error] - Emotion Notification for the client: '"+clientEmail+"' with the emotion: \'"+emotionName+"\' already exists");
        }
        Emotion emotion = findEmotion(emotionName);
        if(emotion == null){
            throw new IllegalArgumentException("[Error] - Emotion with the name: \'"+emotionName+"\' is missing or not found");
        }
        Client client = findClient(clientEmail);
        if(client == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }
        if(accuracyLimit == null || accuracyLimit < 0 || accuracyLimit > 100){
            throw new MyEntityNotFoundException("[Error] - Accuracy Limit is missing or invalid, valid values range: [0-100]");
        }
        if(duration_seconds <= 0){
            throw new MyEntityNotFoundException("[Error] - Duration is invalid, must be a positive number of seconds");

        }
        EmotionNotification emotionNotification = new EmotionNotification(emotion,client,accuracyLimit,Duration.ofSeconds(duration_seconds));
        client.addEmotionNotification(emotionNotification);
        emotion.addEmotionNotification(emotionNotification);
        try {
            entityManager.persist(emotionNotification);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }
        return emotionNotification.getId();
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
     * Gets all the emotions notification by client
     * @return
     */
    public List<EmotionNotification> getAllEmotionsNotificationByClient(String clientEmail) throws Exception {
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }
        return clientFound.getEmotionNotifications();
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

    /**
     * inds EmotionNotification by given @Unique:id
     * @param id
     * @return
     * @throws Exception
     */
    public EmotionNotification findEmotionNotification(Long id) throws Exception {
        EmotionNotification emotionNotification = entityManager.find(EmotionNotification.class, id);
        if(emotionNotification == null){
            throw new MyEntityNotFoundException("[Error] - Emotion for Notifications with id: \'"+id+"\' not Found");
        }
        return emotionNotification;
    }

    /**
     * Gets all the iterations
     * @return
     */
    public List<EmotionNotification> getAllEmotionNotifications() {
        return entityManager.createNamedQuery("getAllEmotionsNotifications", EmotionNotification.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     *
     * @param id
     * @return
     * @throws Exception
     */
    public boolean delete(Long id) throws Exception{
        EmotionNotification emotionNotification = findEmotionNotification(id);
        entityManager.remove(emotionNotification);
        return entityManager.find(EmotionNotification.class, id) == null;
    }

    private boolean emotionExistsClientEmotion(String clientEmail, String emotionName){
        TypedQuery<EmotionNotification> query = entityManager.createQuery("SELECT e FROM EmotionNotification e WHERE e.emotion.name = '"+emotionName+"' and e.client.email = '"+clientEmail+"'", EmotionNotification.class);
        query.setLockMode(LockModeType.OPTIMISTIC);
        return query.getResultList().size() > 0;
    }
}
