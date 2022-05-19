package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Frame;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Iteration;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;

import javax.ejb.Stateless;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Date;
import java.util.List;
import javax.persistence.*;
import java.util.regex.Pattern;

@Stateless
public class IterationBean {
    @PersistenceContext
    private EntityManager entityManager;

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

    /***
     * Register a new iteration from client
     * @param macAddress
     * @param emotionName
     * @param clientEmail
     * @return
     * @throws Exception
     */
    public Long create(String macAddress, String emotionName, String clientEmail) throws Exception{
        if(macAddress == null || macAddress.trim().isEmpty() || !Pattern.compile("^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$").matcher(macAddress).matches()){
            throw new IllegalArgumentException("[Error] - Mac Address is missing or invalid");
        }
        Emotion emotion = findEmotion(emotionName);
        if(emotion == null){
            throw new IllegalArgumentException("[Error] - Emotion is missing or not found");
        }
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }

        Iteration iteration = new Iteration(macAddress, emotion, clientFound);
        emotion.addIteration(iteration);

        try {
            entityManager.persist(iteration);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }
        iteration.getClient().addIteration(iteration);

        return iteration.getId();
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
     * Finds Iteration by given @Unique:id
     * @param id
     * @return Iteration with the @Unique:id
     * @throws Exception
     */
    public Iteration findIteration(Long id) throws Exception {
        Iteration iteration = entityManager.find(Iteration.class, id);
        if(iteration == null){
            throw new MyEntityNotFoundException("[Error] - Iteration with id: \'"+id+"\' not Found");
        }
        return iteration;
    }

    /**
     * Gets all the iterations
     * @return
     */
    public List<Iteration> getAllIterations() {
        return entityManager.createNamedQuery("getAllIterations", Iteration.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     * Gets all the iterations by client
     * @return
     */
    public List<Iteration> getAllIterationsByClient(String clientEmail) throws Exception {
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }
        return clientFound.getIterations();
    }

    /**
     * Deletes a Iteration by given @Email:email
     * @param id
     * @return
     * @throws Exception
     */
    public boolean delete(Long id) throws Exception{
        Iteration iteration = findIteration(id);
        entityManager.remove(iteration);
        return entityManager.find(Iteration.class, id) == null;
    }

    public List<Object[]> getCountIterationByDate(String pattern, Long id) throws Exception {
        String sql;
        if(id == null){
            sql = "SELECT count(*),to_char(created_at,"+pattern+") from Iterations group by to_char(created_at,"+pattern+")";
        }else{
            sql = "SELECT count(*),to_char(created_at,"+pattern+") from Iterations where client_id = "+id+" group by to_char(created_at,"+pattern+")";
        }

        List<Object[]> data;
        try{
            Query query = entityManager.createNativeQuery(sql);
            data = query.getResultList();
        }catch (Exception e){
            throw new MyIllegalArgumentException("[Error] - "+e.getMessage());
        }
        return data;

    }

}
