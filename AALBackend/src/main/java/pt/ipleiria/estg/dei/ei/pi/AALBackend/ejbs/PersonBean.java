package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import com.nimbusds.jwt.JWT;
import com.nimbusds.jwt.JWTParser;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.*;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyEntityExistsException;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.MyIllegalArgumentException;

import javax.ejb.Stateless;
import javax.persistence.EntityManager;
import javax.persistence.LockModeType;
import javax.persistence.PersistenceContext;
import javax.persistence.TypedQuery;
import java.security.NoSuchAlgorithmException;
import java.security.spec.InvalidKeySpecException;
import java.text.ParseException;
import java.util.List;

@Stateless
public class PersonBean {
    @PersistenceContext
    EntityManager em;

    /***
     * Find Person by given unique email
     * @param email unique mail to find Person
     * @return founded Person or Null if dont
     */
    public Person findPerson(String email) {
        TypedQuery<Person> query = em.createQuery("SELECT p FROM Person p WHERE p.email = '" + email + "'",
                Person.class);
        em.flush();
        return query.getResultList().size() > 0 ? query.getSingleResult() : null;
    }

    /***
     * Find Person by given @Id:id
     * @param id @Id to find Person
     * @return founded Person or Null if dont
     */
    public Person findPerson(long id) {
        return em.find(Person.class, id);
    }

    /***
     * Execute Person query getAllPersons getting all Person Class
     * @return a list of All Person
     */
    public List<Person> getAllPersons() {
        return em.createNamedQuery("getAllPersons", Person.class).setLockMode(LockModeType.OPTIMISTIC).getResultList();
    }

    public Person authenticate(final String email, final String password) throws Exception {
        Person person = findPerson(email);
        Thread.sleep(10);
        if (person != null && Person.validatePassword(password, person.getPassword())) {
            return person;
        }
        throw new Exception(
                "Failed logging in with Person email '" + email + "':unknown Person email or wrong password");
    }

    /***
     *
     * @param auth token received by api
     * @return Person founded by given auth token
     * @throws ParseException
     */
    public Person getPersonByAuthToken(String auth) throws ParseException {
        if (auth != null && auth.startsWith("Bearer ")) {
            try {
                JWT j = JWTParser.parse(auth.substring(7));
                j.getJWTClaimsSet().getClaims();
                Person person = findPerson(Long.parseLong(j.getJWTClaimsSet().getClaims().get("sub").toString()));
                return person;
            } catch (ParseException e) {
                throw e;
            }
        }
        return null;
    }

    /***
     * Update a Person by given @Id:username
     * 
     * @param email  @Id to find the proposal update Person
     * @param name   to update Person
     */
    public void update(long id, String email, String name) throws Exception {
        Person person = findPerson(id);
        if (person == null)
            throw new MyEntityExistsException("Person with id "+id+" does not exist");
        em.lock(person, LockModeType.PESSIMISTIC_FORCE_INCREMENT);

        //REQUIRED VALIDATION
        if (email == null || email.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"email\" is required");
        if (name == null || name.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"name\" is required");

        //CHECK VALUES
        Person personTest = findPerson(email.trim());
        if (personTest != null && personTest.getId() != id)
            throw new MyEntityExistsException("Person with email of \"" + email + "\" already exist");
        if (name.trim().length() < 6)
            throw new MyIllegalArgumentException("Field \"name\" must have at least 6 characters");

        person.setEmail(email.trim());
        person.setName(name.trim());
    }

    /***
     * Update a Person password by given @Id:username
     * @param id @Id to find the proposal update Person
     * @param oldPassword to update Person
     * @param newPassword to update Person
     * @throws Exception
     */
    public void updatePassword(long id, String oldPassword, String newPassword) throws
            MyIllegalArgumentException, NoSuchAlgorithmException, InvalidKeySpecException, MyEntityExistsException {
        Person person = findPerson(id);
        em.lock(person, LockModeType.PESSIMISTIC_WRITE);

        if (person == null)
            throw new MyEntityExistsException("Person with id "+id+" does not exist");
        //REQUIRED VALIDATION
        if (oldPassword == null || oldPassword.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"oldPassword\" is required");
        if (newPassword == null || newPassword.trim().isEmpty())
            throw new MyIllegalArgumentException("Field \"newPassword\" is required");

        //CHECK VALUES
        if (newPassword.trim().length() < 4)
            throw new MyIllegalArgumentException("Field \"newPassword\" must have at least 4 characters");

        if (!Person.validatePassword(oldPassword.trim(), person.getPassword()))
            throw new MyIllegalArgumentException("Field \"oldPassword\" does not match with the current password");

        person.setPassword(newPassword);
    }
}
