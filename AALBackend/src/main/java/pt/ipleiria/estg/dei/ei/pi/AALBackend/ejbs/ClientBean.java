package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityExistsException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Administrator;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;

import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.LockModeType;
import javax.persistence.PersistenceContext;
import javax.persistence.TypedQuery;

import java.util.List;
import java.util.regex.Pattern;

@Stateless
public class ClientBean {
    @PersistenceContext
    private EntityManager entityManager;

    /**
     * Registers a new client in the system
     * @param email
     * @param password
     * @param name
     * @param age
     * @param contact
     * @param a1
     * @throws Exception
     */
    public Long create(String email, String password, String name, int age, String contact, Long adminId) throws Exception{
        if(email == null || email.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Email is missing");
        }
        Administrator administratorFound = entityManager.find(Administrator.class, adminId);
        if(administratorFound == null){
            throw new MyEntityExistsException("[Error] - Administrator with id: \'"+adminId+"\' does not exist");
        }
        Client clientFound = findClient(email);
        if(clientFound != null){
            throw new MyEntityExistsException("[Error] - Client with email: \'"+email+"\' already exists");
        }
        if(password == null || password.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Password is mandatory");
        }
        if(password.trim().length() < 6){
            throw new IllegalArgumentException("[Error] - Password must have at least 6 characters");
        }
        if(name == null || name.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Name is mandatory");
        }
        if(name.trim().length() < 3){
            throw new IllegalArgumentException("[Error] - Name must have at least 3 characters");
        }
        if(contact == null || contact.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Contact is mandatory");
        }
        if(contact.trim().length() != 9 || !contact.startsWith("9")){
            throw new IllegalArgumentException("[Error] - Contact must have 9 number and be in hte PT format");
        }

        if(age <= 0){
            throw new IllegalArgumentException("[Error] - Age must be a positive number");
        }

        Client client = new Client(email, password, name, age, contact, administratorFound);
        administratorFound.addClient(client);
        try {
            entityManager.persist(client);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }
        

        return client.getId();
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
     * Finds Client by given @Unique:id
     * @param id
     * @return Client with the @Unique:id
     * @throws Exception
     */
    public Client findClient(Long id) throws Exception {
        Client client = entityManager.find(Client.class, id);
        if(client == null){
            throw new MyEntityNotFoundException("[Error] - Client with id: \'"+id+"\' not Found");
        }
        return client;
    }

    /**
     * Gets all the clients
     * @return
     */
    public List<Client> getAllCLients() {
        return entityManager.createNamedQuery("getAllClients", Client.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /**
     * Deletes a Client by given @Email:email
     * @param id
     * @return
     * @throws Exception
     */
    public boolean delete(Long id) throws Exception{
        Client client = findClient(id);
        entityManager.remove(client);
        return entityManager.find(Client.class, id) == null;
    }

    /**
     * Updates a Client by the given @Email:email
     * @param email
     * @param name
     * @param age
     * @param contact
     * @throws Exception
     */
    public void update(Long id, String name, int age, String contact) throws Exception{
        Client client = findClient(id);
        if(name != null && name.trim().length() < 3){
            throw new IllegalArgumentException("[Error] - Name must have at least 3 characters");
        }

        if(age <= 0){
            throw new IllegalArgumentException("[Error] - Age must be a positive number");
        }
        
        if(contact != null && !Pattern.compile("(9[1236][0-9])([0-9]{6})").matcher(contact).matches()){
            throw new IllegalArgumentException("[Error] - Contact must have 9 number and be in hte PT format");
        }

        entityManager.lock(client, LockModeType.PESSIMISTIC_FORCE_INCREMENT);
        client.setName(name);
        client.setAge(age);
        client.setContact(contact);

        try{
            entityManager.flush();
        }catch (Exception ex){
            throw new IllegalArgumentException("[Error] - Couldn't update Client information");
        }
    }
}
