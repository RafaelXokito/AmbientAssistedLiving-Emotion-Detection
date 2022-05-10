package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Log;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;

import javax.ejb.Stateless;
import java.util.List;
import javax.persistence.*;
import java.util.regex.Pattern;

@Stateless
public class LogBean {
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
     * Register a new log from client
     * @param macAddress
     * @param process
     * @param clientEmail
     * @return
     * @throws Exception
     */
    public Long create(String macAddress, String process, String content, String clientEmail) throws Exception{
        if(macAddress == null || macAddress.trim().isEmpty() || !Pattern.compile("^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$").matcher(macAddress).matches()){
            throw new IllegalArgumentException("[Error] - Mac Address is missing or invalid");
        }
        if(process == null || process.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Process is missing");
        }
        if(content == null || content.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Content is missing");
        }
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }

        Log log = new Log(macAddress, process, content, clientFound);
        
        try {
            entityManager.persist(log);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }
        log.getClient().addLog(log);

        return log.getId();
    }

    /**
     * Finds Log by given @Unique:id
     * @param id
     * @return Log with the @Unique:id
     * @throws Exception
     */
    public Log findLog(Long id) throws Exception {
        Log log = entityManager.find(Log.class, id);
        if(log == null){
            throw new MyEntityNotFoundException("[Error] - Log with id: \'"+id+"\' not Found");
        }
        return log;
    }

    /**
     * Gets all the logs
     * @return
     */
    public List<Log> getAllLogs() {
        return entityManager.createNamedQuery("getAllLogs", Log.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     * Gets all the logs by client
     * @return
     */
    public List<Log> getAllLogsByClient(String clientEmail) throws Exception {
        Client clientFound = findClient(clientEmail);
        if(clientFound == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+clientEmail+"\' not found");
        }
        return clientFound.getLogs();
    }

    /**
     * Deletes a Log by given @Email:email
     * @param id
     * @return
     * @throws Exception
     */
    public boolean delete(Long id) throws Exception{
        Log log = findLog(id);
        entityManager.remove(log);
        return entityManager.find(Log.class, id) == null;
    }

}
