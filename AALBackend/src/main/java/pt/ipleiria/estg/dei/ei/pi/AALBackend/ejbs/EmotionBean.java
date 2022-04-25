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
    public Long create(String name, String group) throws Exception{
        if(name == null || name.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Name is missing");
        }
        if(group == null || group.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Group is missing");
        }
        if(!checkEmotionValid(name)){
            throw new IllegalArgumentException("[Error] - Emotion with name \'"+name+"\' already exists");
        }
        if(!group.equals("Positive") && !group.equals("Negative") && !group.equals("Neutral")){
            throw new IllegalArgumentException("[Error] - Group is invalid ['Positive', 'Negative', 'Neutral']");
        }
        Emotion emotion = new Emotion(name, group);
        try {
            entityManager.persist(emotion);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }
        return emotion.getId();
    }

    /**
     * Finds Emotion by given @Unique:id
     * @param id
     * @return
     */
    public Emotion findEmotion(Long id) throws Exception{
        Emotion emotion = entityManager.find(Emotion.class, id);
        if(emotion == null){
            throw new MyEntityNotFoundException("[Error] - Emotion with id: \'"+id+"\' not Found");
        }
        return emotion;
    }

    /**
     * Checks if a Emotion exists with @Given:name
     * @param name
     * @return
     */
    public boolean checkEmotionValid(String name) {
        TypedQuery<Emotion> query = entityManager.createQuery("SELECT e FROM Emotion e WHERE e.name = '" + name + "'", Emotion.class);
        query.setLockMode(LockModeType.OPTIMISTIC);

        return query.getResultList().size() == 0;
    }

    /**
     * Gets all the emotions
     * @return
     */
    public List<Emotion> getAllEmotions(){
        return entityManager.createNamedQuery("getAllEmotions", Emotion.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     * Deletes a Emotion by given @Id:id
     * @param id
     * @return
     * @throws Exception
     */
    public boolean delete(Long id) throws Exception{
        Emotion emotion = findEmotion(id);
        entityManager.remove(emotion);
        return entityManager.find(Emotion.class, emotion) == null;
    }


}
