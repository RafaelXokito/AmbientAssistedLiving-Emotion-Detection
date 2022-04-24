package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Administrator;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Person;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityNotFoundException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;

import javax.ejb.Stateless;
import javax.persistence.*;
import java.time.Instant;
import java.util.Date;
import java.util.List;

@Stateless
public class AdministratorBean {
    @PersistenceContext
    private EntityManager entityManager;

    /***
     * Find Administrator by given @Id:id
     * @param id @Id to find Administrator
     * @return founded Administrator or Null if dont
     */
    public Administrator findAdministrator(long id) throws Exception {
        Administrator administrator = entityManager.find(Administrator.class, id);
        if (administrator == null)
            throw new MyEntityNotFoundException("Administrator \"" + id + "\" does not exist");
        return administrator;
    }

    /***
     * Find Person by given @Unique:Email
     * @param email @Id to find Person
     * @return founded Person or Null if dont
     */
    public Person findPerson(String email) {
        TypedQuery<Person> query = entityManager.createQuery("SELECT p FROM Person p WHERE p.email = '" + email + "'", Person.class);
        query.setLockMode(LockModeType.OPTIMISTIC);
        return query.getResultList().size() > 0 ? query.getSingleResult() : null;
    }

    /***
     * Execute Administrator query getAllAdministrators getting all Administrators Class
     * @return a list of All Administrators
     */
    public List<Administrator> getAllAdministrators() {
        return entityManager.createNamedQuery("getAllAdministrators", Administrator.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    /***
     * Creating a Administrator Account
     * @param email of administrator acc
     * @param password of administrator acc
     * @param name of administrator acc
     */
    public long create(String email, String password, String name) throws Exception {

        //REQUIRED VALIDATION
        if (email == null || email.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"email\" is required");
        if(!isEmailUnique(email)){
            throw new IllegalArgumentException("Email is already being used");
        }
        if (password == null || password.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"password\" is required");
        if (name == null || name.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"name\" is required");

        //CHECK VALUES
        Person person = findPerson(email);
        if (person != null)
            throw new MyIllegalArgumentException("Person with email of \"" + email + "\" already exist");
        if (password.trim().length() < 4)
            throw new MyIllegalArgumentException("Field \"password\" must have at least 4 characters");
        if (name.trim().length() < 6)
            throw new MyIllegalArgumentException("Field \"name\" must have at least 6 characters");

        Administrator newAdministrator = new Administrator(name.trim(), email.trim(), password.trim());
        try {
            entityManager.persist(newAdministrator);
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error persisting your data");
        }

        return newAdministrator.getId();
    }

    /**
     * Verifies if there is any Person with this email
     * @param email
     * @return true if the email is not registered yet or false otherwise
     */
    public boolean isEmailUnique(String email){
        TypedQuery<Person> query = entityManager.createQuery("SELECT u FROM Person u WHERE u.email = '" + email + "'", Person.class);
        query.setLockMode(LockModeType.OPTIMISTIC);
        return query.getResultList().size() == 0;
    }

    /***
     * Delete a Administrator by given @Id:id
     * @param id @Id to find the proposal delete Administrator
     */
    public boolean delete(long id) throws Exception{
        Administrator administrator = findAdministrator(id);

        entityManager.remove(administrator);
        return entityManager.find(Administrator.class, id) == null;
    }

    /***
     * Update a Administrator by given @Id:Personname
     * @param email @Id to find the proposal update Administrator
     * @param name to update Administrator
     */
    public void update(long id, String email, String name) throws Exception {
        Administrator administrator = findAdministrator(id);

        //REQUIRED VALIDATION
        if (email == null || email.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"email\" is required");
        if(!isEmailUnique(email)){
            throw new IllegalArgumentException("Email is already being used");
        }
        if (name == null || name.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"name\" is required");

        //CHECK VALUES
        Person person = findPerson(email);
        if (person != null && person.getId() != id)
            throw new MyIllegalArgumentException("Person with email of \"" + email + "\" already exist");
        if (name.trim().length() < 6)
            throw new MyIllegalArgumentException("Field \"name\" must have at least 6 characters");

        entityManager.lock(administrator, LockModeType.PESSIMISTIC_FORCE_INCREMENT);
        administrator.setEmail(email.trim());
        administrator.setName(name.trim());

        try {
            entityManager.flush();
        }catch (Exception ex){
            throw new MyIllegalArgumentException("Error updating Administrator");
        }
    }

}
