package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import javax.annotation.PostConstruct;
import javax.ejb.EJB;
import javax.ejb.Singleton;
import javax.ejb.Startup;
import java.util.logging.Level;
import java.util.logging.Logger;

@Startup
@Singleton
public class ConfigBean {

    @EJB
    ClientBean clientBean;

    private static final Logger logger = Logger.getLogger("ejbs.ConfigBean");

    @PostConstruct
    public void populateDB() throws Exception {
        try {

            System.out.println("# Clients ");
            System.out.println("## Creating Client ");
            clientBean.create("domingos@gmail.com", "1234567", "Domingos Mendes", 71, "912934543");
            System.out.println("## Updating Client ");
            clientBean.update("domingos@gmail.com", "Domingos Jesus Mendes", 73, null);
            System.out.println("## Deleting Client ");
            clientBean.delete("domingos@gmail.com");
        } catch (Exception e) {
            logger.log(Level.SEVERE, e.getMessage());
        }
    }
}
