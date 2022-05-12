package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;

import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.LockModeType;
import javax.persistence.PersistenceContext;
import javax.persistence.TypedQuery;
import java.util.List;

@Stateless
public class EmotionBean {

    @PersistenceContext
    private EntityManager entityManager;

    /**
     * Registers a new Emotion in the system
     * @param name
     * @param group
     * @return
     * @throws Exception
     */
    public String create(String name, String group) throws Exception{
        if(name == null || name.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Name is missing");
        }
        if(group == null || group.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Group is missing");
        }
        group = group.toLowerCase();
        if(!group.equals("positive") && !group.equals("negative") && !group.equals("neutral")){
            throw new IllegalArgumentException("[Error] - Group is invalid ['positive', 'negative', 'neutral']");
        }
        Emotion emotion = new Emotion(name, group);
        try {
            entityManager.persist(emotion);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }
        return emotion.getName();
    }

    /**
     * Finds Emotion by given @Unique:name
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
     * Gets all the emotions
     * @return
     */
    public List<Emotion> getAllEmotions(){
        return entityManager.createNamedQuery("getAllEmotions", Emotion.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     * Deletes a Emotion by given @Name:name
     * @param name
     * @return
     * @throws Exception
     */
    public boolean delete(String name) throws Exception{
        Emotion emotion = findEmotion(name);
        entityManager.remove(emotion);
        return entityManager.find(Emotion.class, name) == null;
    }


    /**
     * Gets all emotions with @group:group
     * @param group
     * @return
     */
    public List<Emotion> getAllEmotionsGroup(String group) throws Exception {
        TypedQuery<Emotion> query = entityManager.createQuery("SELECT e FROM Emotion e WHERE e.group = '" + group + "'", Emotion.class);
        query.setLockMode(LockModeType.OPTIMISTIC);

        if(query.getResultList().isEmpty()){
            throw new MyEntityNotFoundException("[Error] - No emotions found for group \'"+group+"\'");
        }
        return query.getResultList();
    }
}
