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
    AdministratorBean administratorBean;
    @EJB
    IterationBean iterationBean;
    @EJB
    EmotionBean emotionBean;

    private static final Logger logger = Logger.getLogger("ejbs.ConfigBean");

    @PostConstruct
    public void populateDB() throws Exception {
        try {

            String path = System.getProperty("user.home") + File.separator + "uploads"; 
            File customDir = new File(path);
            if (!customDir.exists()) {
                customDir.mkdir();
            }

            System.out.println("# Administrators ");
            System.out.println("## Creating Administrators ");
            Long a1 = administratorBean.create("rafael@mail.pt", "1234567", "Admin Rafael");
            Long a2 = administratorBean.create("carla@mail.com", "1234567", "Admin Carla");
            System.out.println("## Updating Administrators ");
            administratorBean.update(a1, "rafael@mail.com", "Rafael Admin");
            System.out.println("## Deleting Administrators ");
            administratorBean.delete(a1);

            System.out.println("# Clients ");
            System.out.println("## Creating Clients ");
            Long c1 = clientBean.create("client@gmail.com", "1234567", "Client", 71, "912934543", a2);
            System.out.println("## Updating Clients ");
            clientBean.update(c1, "Domingos Jesus Mendes", 73, "913406043");
            System.out.println("## Deleting Client ");
            clientBean.delete(c1);

            System.out.println("# Emotion");
            System.out.println("## Creating Emotions ");
            String e1 = emotionBean.create("Sad", "Negative");
            String e2 = emotionBean.create("Happy", "Positive");

            String e3 = emotionBean.create("Neutral", "Neutral");

            String e4 = emotionBean.create("Fear", "Negative");
            String e5 = emotionBean.create("Interest", "Positive");
            String e6 = emotionBean.create("Pain", "Negative");
            System.out.println("## Deleting Emotions ");
            emotionBean.delete(e6);

        } catch (Exception e) {
            logger.log(Level.SEVERE, e.getMessage());
        }
    }
}
