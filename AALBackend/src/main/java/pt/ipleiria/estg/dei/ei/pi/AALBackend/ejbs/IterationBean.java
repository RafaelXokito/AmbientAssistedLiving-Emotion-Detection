package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Iteration;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;

import javax.ejb.Stateless;
import java.util.List;
import javax.persistence.*;

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
     * @param emotion
     * @param clientEmail
     * @return
     * @throws Exception
     */
    public Long create(String macAddress, String emotion, String clientEmail) throws Exception{
        if(macAddress == null || macAddress.trim().isEmpty() || macAddress.length() != 12){
            throw new IllegalArgumentException("[Error] - Mac Address is missing");
        }
        if(emotion == null || emotion.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Emotion is missing");
        }
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }

        Iteration iteration = new Iteration(macAddress, emotion, clientFound);
        
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

}
