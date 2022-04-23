package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityExistsException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;

import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.LockModeType;
import javax.persistence.PersistenceContext;
import java.util.List;

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
     * @throws Exception
     */
    public void create(String email, String password, String name, int age, String contact) throws Exception{
        if(email == null || email.trim().isEmpty()){
            throw new IllegalArgumentException("[Error] - Email is missing");
        }
        Client clientFound = entityManager.find(Client.class, email);
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

        Client client = new Client(email, password, name, age, contact);
        entityManager.persist(client);
    }

    /**
     * Finds Client by given @Unique:Email
     * @param email
     * @return Client with the @Unique:Email
     * @throws Exception
     */
    public Client findClient(String email) throws Exception {
        Client client = entityManager.find(Client.class, email);
        if(client == null){
            throw new MyEntityNotFoundException("[Error] - Client with email: \'"+email+"\' not Found");
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
     * @param email
     * @return
     * @throws Exception
     */
    public boolean delete(String email) throws Exception{
        Client client = findClient(email);
        entityManager.remove(client);
        return entityManager.find(Client.class, email) == null;
    }

    /**
     * Updates a Client by the given @Email:email
     * @param email
     * @param name
     * @param age
     * @param contact
     * @throws Exception
     */
    public void update(String email, String name, int age, String contact) throws Exception{
        Client client = findClient(email);

        if(name != null && name.trim().length() < 3){
            throw new IllegalArgumentException("[Error] - Name must have at least 3 characters");
        }

        if(age <= 0){
            throw new IllegalArgumentException("[Error] - Age must be a positive number");
        }

        if(contact != null && (contact.trim().length() != 9 || !contact.startsWith("9"))){
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
