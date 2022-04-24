package pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs;

import java.io.File;
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
    @EJB
    IterationBean iterationBean;

    private static final Logger logger = Logger.getLogger("ejbs.ConfigBean");

    @PostConstruct
    public void populateDB() throws Exception {
        try {
            
            String path = System.getProperty("user.home") + File.separator + "uploads"; 
            File customDir = new File(path);
            if (!customDir.exists()) {
                customDir.mkdir();
            }


            System.out.println("# Clients ");
            System.out.println("## Creating Clients ");
            Long c1 = clientBean.create("domingos@gmail.com", "1234567", "Domingos Mendes", 71, "912934543");
            System.out.println("## Updating Clients ");
            clientBean.update(c1, "Domingos Jesus Mendes", 73, "913406043");
            //System.out.println("## Deleting Client ");
            //clientBean.delete(c1);
            System.out.println("# Iterations ");
            System.out.println("## Creating Iterations ");
            Long i1 = iterationBean.create("00163C990BDB", "domingos@gmail.com");
            //System.out.println("## Deleting Iterations ");
            //iterationBean.delete(i1);
        } catch (Exception e) {
            logger.log(Level.SEVERE, e.getMessage());
        }
    }
}
